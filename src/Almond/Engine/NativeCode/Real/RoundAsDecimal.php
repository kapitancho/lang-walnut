<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, IntegerType, IntegerValue|RealValue, IntegerValue> */
final readonly class RoundAsDecimal extends NativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		if (!parent::isParameterTypeValid($parameterType, $validator, $targetType)) {
			return false;
		}
		/** @var IntegerType $parameterType */
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(0));
	}

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, IntegerType $parameterType): RealType {
			return $this->typeRegistry->real(
				$targetType->numberRange->min === MinusInfinity::value ? MinusInfinity::value :
					$targetType->numberRange->min->value->floor(),
				$targetType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					$targetType->numberRange->max->value->ceil()
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, IntegerValue $parameter): RealValue =>
			$this->valueRegistry->real(
				$target->literalValue->round((int)(string)$parameter->literalValue)
			);
	}
}
