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
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<IntegerType|RealType, Type, Value> */
final readonly class WithRange extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		return $targetRefType instanceof IntegerType || $targetRefType instanceof RealType ?
			null : sprintf("Target ref type must be an Integer or Real type, got: %s", $targetRefType);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		if ($targetType->refType instanceof IntegerType) {
			return $parameterType->isSubtypeOf($this->typeRegistry->core->integerRange) ?
				null : sprintf("Parameter type must be a subtype of IntegerRange, got: %s", $parameterType);
		}
		return $parameterType->isSubtypeOf($this->typeRegistry->core->integerRange) ||
			$parameterType->isSubtypeOf($this->typeRegistry->core->realRange) ?
			null : sprintf("Parameter type must be a subtype of IntegerRange or RealRange, got: %s", $parameterType);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, Type $parameterType): TypeType {
			/** @var IntegerType|RealType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				return $this->typeRegistry->type($this->typeRegistry->integer());
			}
			/** @var RealType $refType */
			return $this->typeRegistry->type($this->typeRegistry->real());
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
