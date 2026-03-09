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

/** @extends NativeMethod<RealType, IntegerType, RealValue, IntegerValue> */
final readonly class RoundAsDecimal extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var IntegerType $parameterType */
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(0)) ?
			null :
			sprintf(
				"The parameter type should be a subtype of Integer<0..>, got %s.",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(RealType $targetType, IntegerType $parameterType): RealType {
			return $this->typeRegistry->real(
				$targetType->numberRange->min === MinusInfinity::value ? MinusInfinity::value :
					$targetType->numberRange->min->value->floor(),
				$targetType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					$targetType->numberRange->max->value->ceil()
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(RealValue $target, IntegerValue $parameter): RealValue =>
			$this->valueRegistry->real(
				$target->literalValue->round((int)(string)$parameter->literalValue)
			);
	}
}
