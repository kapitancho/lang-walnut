<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

/** @extends NativeMethod<FunctionType, TypeInterface, FunctionValue, Value> */
final readonly class ForceInvoke extends NativeMethod {
	use TupleAsRecord;

	protected function getValidator(): callable {
		return function(FunctionType $targetType, TypeInterface $parameterType, mixed $origin): TypeInterface|ValidationFailure {
			$p = $targetType->parameterType;
			$parameterType = $this->adjustParameterType(
				$this->typeRegistry,
				$p,
				$parameterType,
			);
			return $parameterType->isSubtypeOf($targetType->parameterType) ?
				$targetType->returnType :
				$this->typeRegistry->result(
					$targetType->returnType,
					$this->typeRegistry->core->invocationError
				);
		};
	}

	protected function getExecutor(): callable {
		return function(FunctionValue $target, Value $parameter): Value {
			$parameter = $this->adjustParameterValue(
				$this->valueRegistry,
				$target->type->parameterType,
				$parameter,
			);
			if ($parameter->type->isSubtypeOf($target->type->parameterType)) {
				return $target->execute($parameter);
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->invocationError(
					$this->valueRegistry->record([
						'functionType' => $this->valueRegistry->type($target->type),
						'providedParameterType' => $this->valueRegistry->type($parameter->type),
						'errorMessage' => $this->valueRegistry->string(
							sprintf(
								"Invalid parameter type: %s, %s expected (function is %s)",
								$parameter->type,
								$target->type->parameterType,
								$target->type
							)
						),
					])
				)
			);

		};
	}
}