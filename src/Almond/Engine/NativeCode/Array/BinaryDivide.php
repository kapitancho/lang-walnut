<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, IntegerType, IntegerValue> */
final readonly class BinaryDivide extends ArrayNativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator): bool|Type {
		if (!parent::isParameterTypeValid($parameterType, $validator)) {
			return false;
		}
		/** @var IntegerType $parameterType */
		return $parameterType->numberRange->min !== MinusInfinity::value &&
			$parameterType->numberRange->min->value > 0;
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, IntegerType $parameterType): Type {
			$minL = $targetType->range->minLength;
			$maxL = $targetType->range->maxLength;
			$minS = $parameterType->numberRange->min->value;
			$maxS = $parameterType->numberRange->max === PlusInfinity::value ?
				PlusInfinity::value : $parameterType->numberRange->max->value;

			$minI = $minL > 0 ? 1 : 0;
			$maxI = $maxL < $maxS ? $maxL : $minS;

			$minO = match(true) {
				$maxS !== PlusInfinity::value => $minL->div($maxS)->ceil(),
				$minL < 1 => 0,
				default => 1,
			};
			$maxO = match(true) {
				$maxL === PlusInfinity::value => PlusInfinity::value,
				$minS > 0 => $maxL->div($minS)->ceil(),
				// @codeCoverageIgnoreStart
				default => PlusInfinity::value,
				// @codeCoverageIgnoreEnd
			};

			return $this->typeRegistry->array(
				$this->typeRegistry->array(
					$targetType->itemType,
					$minI,
					$maxI
				),
				$minO,
				$maxO
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, IntegerValue $parameter): TupleValue {
			$chunkSize = (int)(string)$parameter->literalValue;
			$chunks = array_chunk($target->values, $chunkSize);
			return $this->valueRegistry->tuple(
				array_map(
					fn(array $chunk): TupleValue =>
					$this->valueRegistry->tuple($chunk),
					$chunks
				)
			);
		};
	}

}
