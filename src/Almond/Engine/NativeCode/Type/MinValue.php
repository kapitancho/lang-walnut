<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<IntegerType|RealType, NullType, NullValue> */
final readonly class MinValue extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof IntegerType || $refType instanceof RealType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): Type {
			/** @var IntegerType|RealType $refType */
			$refType = $this->toBaseType($targetType->refType);
			return $this->typeRegistry->union([
				$refType instanceof IntegerType ?
					$this->typeRegistry->integer() :
					$this->typeRegistry->real(),
				$this->typeRegistry->core->minusInfinity
			]);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): Value {
			/** @var IntegerType|RealType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof IntegerType) {
				return $typeValue->numberRange->min === MinusInfinity::value ?
					$this->valueRegistry->core->minusInfinity :
					$this->valueRegistry->integer($typeValue->numberRange->min->value);
			}
			return $typeValue->numberRange->min === MinusInfinity::value ?
				$this->valueRegistry->core->minusInfinity :
				$this->valueRegistry->real($typeValue->numberRange->min->value);
		};
	}

}
