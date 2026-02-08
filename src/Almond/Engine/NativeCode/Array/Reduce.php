<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Reduce implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->record([
					'reducer' => $this->typeRegistry->function(
						$this->typeRegistry->record([
							'result' => $this->typeRegistry->nothing,
							'item' => $type->itemType,
						], null),
						$this->typeRegistry->any
					),
					'initial' => $this->typeRegistry->any,
				], null)
			)) {
				$parameterType = $this->toBaseType($parameterType);
				$reducerType = $this->toBaseType($parameterType->types['reducer']);
				$initialType = $this->toBaseType($parameterType->types['initial']);

				$reducerParamType = $this->toBaseType($reducerType->parameterType);
				$resultType = $reducerParamType->types['result'] ?? $this->typeRegistry->any;

				$reducerReturnType = $this->toBaseType($reducerType->returnType);
				$reducerReturnErrorType = $reducerReturnType instanceof ResultType ?
					$reducerReturnType->errorType : null;
				$reducerReturnReturnType = $reducerReturnType instanceof ResultType ?
					$reducerReturnType->returnType : $reducerReturnType;

				if (!$reducerReturnReturnType->isSubtypeOf($resultType)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"[%s] Reducer return type %s must match result type %s",
							__CLASS__,
							$reducerReturnReturnType,
							$resultType
						),
						origin: $origin
					);
				}
				if (!$initialType->isSubtypeOf($resultType)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"[%s] Initial value type %s must match result type %s",
							__CLASS__,
							$initialType,
							$resultType
						),
						origin: $origin
					);
				}
				return $this->validationFactory->validationSuccess(
					$reducerReturnErrorType ?
						$this->typeRegistry->result($resultType, $reducerReturnErrorType) :
						$resultType
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Parameter must be a record with 'reducer' and 'initial' fields", __CLASS__),
				origin: $origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof TupleValue) {
			if ($parameter instanceof RecordValue) {
				if (isset($parameter->values['reducer']) && isset($parameter->values['initial'])) {
					$reducer = $parameter->values['reducer'];
					$accumulator = $parameter->values['initial'];

					if ($reducer instanceof FunctionValue) {
						foreach ($target->values as $item) {
							$reducerParam = $this->valueRegistry->record(['result' => $accumulator, 'item' => $item]);
							$accumulator = $reducer->execute($reducerParam);
							if ($accumulator instanceof ErrorValue) {
								break;
							}
						}
						return $accumulator;
					}
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
