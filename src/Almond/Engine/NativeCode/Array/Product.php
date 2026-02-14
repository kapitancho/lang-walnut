<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, NullType, TupleValue, NullValue> */
final readonly class Product extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		/** @var ArrayType $type */
		$itemType = $this->toBaseType($type->itemType);
		return $itemType->isSubtypeOf(
			$this->typeRegistry->union([
				$this->typeRegistry->integer(),
				$this->typeRegistry->real()
			])
		);
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, NullType $parameterType): Type {
			$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$itemType = $this->toBaseType($targetType->itemType);
			if ($itemType instanceof RealType || $itemType instanceof IntegerType) {
				$start = match(true) {
					$itemType->numberRange->min !== MinusInfinity::value &&
						$itemType->numberRange->min->value > 0 => new NumberIntervalEndpoint(new Number(0), false),
					default => MinusInfinity::value,
				};
				$end = match(true) {
					$itemType->numberRange->max !== PlusInfinity::value && $targetType->range->maxLength !== PlusInfinity::value =>
					new NumberIntervalEndpoint(
						$itemType->numberRange->max->value->pow($targetType->range->maxLength),
						$itemType->numberRange->max->inclusive
					),
					default => PlusInfinity::value,
				};
				$interval = new NumberInterval($start, $end);
				return $itemType instanceof RealType ?
					$this->typeRegistry->realFull($interval) :
					$this->typeRegistry->integerFull($interval);
			}
			return $this->typeRegistry->real();
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): Value {
			$product = 1;
			$hasReal = false;
			foreach ($target->values as $item) {
				$v = $item->literalValue;
				if (str_contains((string)$v, '.')) {
					$hasReal = true;
				}
				$product *= $v;
			}
			return $hasReal ? $this->valueRegistry->real($product) : $this->valueRegistry->integer($product);
		};
	}

}
