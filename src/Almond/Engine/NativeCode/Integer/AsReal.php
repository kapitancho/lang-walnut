<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class AsReal extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType $targetType, NullType $parameterType): RealSubsetType|RealType {
			if ($targetType instanceof IntegerSubsetType || $targetType instanceof RealSubsetType) {
				return $this->typeRegistry->realSubset($targetType->subsetValues);
			}
			return $this->typeRegistry->realFull(...
				$targetType->numberRange->intervals
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): RealValue =>
			$this->valueRegistry->real((string)$target->literalValue);
	}
}
