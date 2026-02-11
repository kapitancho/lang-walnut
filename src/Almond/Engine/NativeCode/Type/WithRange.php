<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<IntegerType|RealType, Type, Value> */
final readonly class WithRange extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof IntegerType || $refType instanceof RealType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, Type $parameterType, mixed $origin): TypeType|ValidationFailure {
			/** @var IntegerType|RealType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf($this->typeRegistry->core->integerRange)) {
					return $this->typeRegistry->type($this->typeRegistry->integer());
				}
			} elseif ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf($this->typeRegistry->core->realRange)) {
					return $this->typeRegistry->type($this->typeRegistry->real());
				}
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, Value $parameter): TypeValue {
			/** @var IntegerType|RealType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof IntegerType) {
				$range = $parameter->value->values;
				$minValue = $range['minValue'];
				$maxValue = $range['maxValue'];
				$result = $this->typeRegistry->integer(
					$minValue instanceof IntegerValue ? $minValue->literalValue : MinusInfinity::value,
					$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
				);
				return $this->valueRegistry->type($result);
			}
			$range = $parameter->value->values;
			$minValue = $range['minValue'];
			$maxValue = $range['maxValue'];
			$result = $this->typeRegistry->real(
				$minValue instanceof RealValue || $minValue instanceof IntegerValue ? $minValue->literalValue : MinusInfinity::value,
				$maxValue instanceof RealValue || $maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
