<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<ArrayType|TupleType, TupleValue> */
final readonly class ZipMap extends SetNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, Expression|null $origin): bool {
		return $targetItemType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return function(SetType $targetType, ArrayType|TupleType $parameterType): MapType {
			$parameterType = $parameterType instanceof TupleType ? $parameterType->asArrayType() : $parameterType;
			return $this->typeRegistry->map(
				$parameterType->itemType,
				min($targetType->range->minLength, $parameterType->range->minLength),
				match(true) {
					$targetType->range->maxLength === PlusInfinity::value => $parameterType->range->maxLength,
					$parameterType->range->maxLength === PlusInfinity::value => $targetType->range->maxLength,
					default => min($targetType->range->maxLength, $parameterType->range->maxLength)
				},
				$targetType->itemType
			);
		};
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, TupleValue $parameter): Value {
			$pValues = $parameter->values;
			$result = [];
			foreach ($target->values as $value) {
				/** @var StringValue $value */
				if (count($pValues) === 0) {
					break;
				}
				$pValue = array_shift($pValues);
				$result[$value->literalValue] = $pValue;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
