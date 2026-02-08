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
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<RealType|IntegerType, NullType, NullValue> */
final readonly class MaxValue extends TypeNativeMethod {

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): Type {
			/** @var RealType|IntegerType $refType */
			$refType = $this->toBaseType($targetType->refType);
			return $this->typeRegistry->union([
				$refType instanceof IntegerType ?
					$this->typeRegistry->integer() :
					$this->typeRegistry->real(),
				$this->typeRegistry->core->plusInfinity
			]);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): Value {
			/** @var RealType|IntegerType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof IntegerType) {
				return $typeValue->numberRange->max === PlusInfinity::value ?
					$this->valueRegistry->core->plusInfinity :
					$this->valueRegistry->integer($typeValue->numberRange->max->value);
			} else {
				return $typeValue->numberRange->max === PlusInfinity::value ?
					$this->valueRegistry->core->plusInfinity :
					$this->valueRegistry->real($typeValue->numberRange->max->value);
			}
		};
	}

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		return $targetRefType instanceof IntegerType || $targetRefType instanceof RealType;
	}

}
