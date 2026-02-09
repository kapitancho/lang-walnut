<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NumericRangeHelper;

/** @extends NativeMethod<IntegerType|RealType, IntegerType|RealType, IntegerValue|RealValue, IntegerValue|RealValue> */
final readonly class BinaryModulo extends NativeMethod {
	use NumericRangeHelper;

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, IntegerType|RealType $parameterType): Type {
			$subsetType = $this->getModuloSubsetType(
				$targetType, $parameterType
			);
			if ($subsetType !== null) {
				return $subsetType;
			}

			$includesZero = $parameterType->contains(0);
			$returnType = $this->typeRegistry->real();
			return $includesZero ? $this->typeRegistry->result(
				$returnType,
				$this->typeRegistry->core->notANumber
			) : $returnType;
		};
	}

	protected function getExecutor(): callable {
		return function(IntegerValue|RealValue $target, IntegerValue|RealValue $parameter): Value {
			if ((float)(string)$parameter->literalValue === 0.0) {
				return $this->valueRegistry->error(
					$this->valueRegistry->core->notANumber
				);
			}
			return $this->valueRegistry->real(
				fmod((float)(string)$target->literalValue, (float)(string)$parameter->literalValue)
			);
		};
	}
}
