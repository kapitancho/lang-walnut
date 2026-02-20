<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<StringType|ArrayType|MapType|SetType, Type, Value> */
final readonly class WithLengthRange extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		return $targetRefType instanceof StringType || $targetRefType instanceof ArrayType ||
			$targetRefType instanceof MapType || $targetRefType instanceof SetType ?
			null :
			sprintf("Target ref type must be a String type, an Array type, a Map type or a Set type, got: %s", $targetRefType);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->core->lengthRange
		) ? null : sprintf("Parameter type must be a LengthRange type, got: %s", $parameterType);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, Type $parameterType): TypeType {
			/** @var StringType|ArrayType|MapType|SetType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof StringType) {
				return $this->typeRegistry->type($this->typeRegistry->string());
			}
			if ($refType instanceof ArrayType) {
				return $this->typeRegistry->type($this->typeRegistry->array($refType->itemType));
			}
			if ($refType instanceof MapType) {
				return $this->typeRegistry->type($this->typeRegistry->map($refType->itemType, 0, PlusInfinity::value, $refType->keyType));
			}
			return $this->typeRegistry->type($this->typeRegistry->set($refType->itemType));
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, Value $parameter): TypeValue {
			/** @var StringType|ArrayType|MapType|SetType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			$range = $parameter->value->values;
			$minValue = $range['minLength'];
			$maxValue = $range['maxLength'];

			if ($typeValue instanceof StringType) {
				$result = $this->typeRegistry->string(
					$minValue->literalValue,
					$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
				);
				return $this->valueRegistry->type($result);
			}
			if ($typeValue instanceof ArrayType) {
				$result = $this->typeRegistry->array(
					$typeValue->itemType,
					$minValue->literalValue,
					$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
				);
				return $this->valueRegistry->type($result);
			}
			if ($typeValue instanceof MapType) {
				$result = $this->typeRegistry->map(
					$typeValue->itemType,
					$minValue->literalValue,
					$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					$typeValue->keyType
				);
				return $this->valueRegistry->type($result);
			}
			/** @var SetType $typeValue */
			$result = $this->typeRegistry->set(
				$typeValue->itemType,
				$minValue->literalValue,
				$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
