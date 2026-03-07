<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, IntegerType, IntegerValue> */
final readonly class BinaryIntegerDivide extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(1)) ?
			null : "The parameter type should be a subtype of Integer<1..>";
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, IntegerType $parameterType): Type {
			$minL = $targetType->range->minLength;
			$maxL = $targetType->range->maxLength;
			$minS = $parameterType->numberRange->min->value;
			$maxS = $parameterType->numberRange->max === PlusInfinity::value ?
				PlusInfinity::value : $parameterType->numberRange->max->value;

			$minO = $maxS !== PlusInfinity::value ? $minL->div($maxS)->floor() : 0;
			$maxO = match(true) {
				$maxL === PlusInfinity::value => PlusInfinity::value,
				$minS > 0 => $maxL->div($minS)->floor(),
				// @codeCoverageIgnoreStart
				default => PlusInfinity::value,
				// @codeCoverageIgnoreEnd
			};

			return $this->typeRegistry->array(
				$this->typeRegistry->array(
					$targetType->itemType,
					$minS,
					$maxS
				),
				$minO,
				$maxO
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, IntegerValue $parameter): TupleValue {
			/** @var int<1, max> $chunkSize */
			$chunkSize = (int)(string)$parameter->literalValue;
			$chunks = array_chunk($target->values, $chunkSize);
			if (count($chunks) > 0 && count($chunks[array_key_last($chunks)]) < $chunkSize) {
				array_pop($chunks);
			}
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
