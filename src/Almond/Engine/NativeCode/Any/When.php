<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

/** @extends NativeMethod<Type, RecordType, Value, RecordValue> */
final readonly class When extends NativeMethod {
	use TupleAsRecord;

	protected function getValidator(): callable {
		return function(Type $targetType, RecordType $parameterType, mixed $origin): Type|ValidationFailure {
			$targetReturnType = match(true) {
				$targetType instanceof ResultType => $targetType->returnType,
				default => $this->typeRegistry->any,
			};
			$errorType = match(true) {
				$targetType instanceof ResultType => $targetType->errorType,
				$targetType instanceof AnyType => $this->typeRegistry->any,
				default => $this->typeRegistry->nothing,
			};

			// Build the expected parameter type: [success: ^T => R1, error: ^E => R2]
			$refType = $this->typeRegistry->record([
				'success' => $this->typeRegistry->function(
					$targetReturnType,
					$this->typeRegistry->any
				),
				'error' => $this->typeRegistry->function(
					$errorType,
					$this->typeRegistry->any
				)
			], null);

			// Validate the parameter type and extract return types
			$pType = $this->toBaseType($parameterType);
			if ($pType->isSubtypeOf($refType)) {
				$successType = $pType->types['success']->returnType;
				$errorType = $pType->types['error']->returnType;

				// Return type is the union of both callback return types
				return $this->typeRegistry->union([$successType, $errorType]);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("Invalid parameter type: %s, a subtype of %s expected",
					$parameterType, $refType
				),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(Value $target, RecordValue $parameter): Value {
			// Extract the success callback
			$successFn = $parameter->values['success'] ?? null;
			if (!$successFn instanceof FunctionValue) {
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value for 'success' key");
				// @codeCoverageIgnoreEnd
			}

			// Extract the error callback
			$errorFn = $parameter->values['error'] ?? null;
			if (!$errorFn instanceof FunctionValue) {
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value for 'error' key");
				// @codeCoverageIgnoreEnd
			}

			// Apply the appropriate callback based on whether target is an error
			return $target instanceof ErrorValue ?
				$errorFn->execute(
					$target->errorValue
				) : $successFn->execute(
					$target
				);
		};
	}
}