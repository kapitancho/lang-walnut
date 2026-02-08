<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, IntegerType, StringValue, IntegerValue> */
readonly class StringChunk extends NativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator): bool {
		if (!parent::isParameterTypeValid($parameterType, $validator)) {
			return false;
		}
		/** @var IntegerType $parameterType */
		return $parameterType->numberRange->min !== MinusInfinity::value &&
			$parameterType->numberRange->min->value >= 1;
	}

	protected function getValidator(): callable {
		return fn(StringType $targetType, IntegerType $parameterType, Expression|null $origin): ArrayType =>
			$this->typeRegistry->array(
				$this->typeRegistry->string(
					min(1, $targetType->range->minLength),
					$parameterType->numberRange->max === PlusInfinity::value ?
						PlusInfinity::value : $parameterType->numberRange->max->value
				),
				match(true) {
					$parameterType->numberRange->max === PlusInfinity::value =>
					$targetType->range->minLength > 0 ? 1 : 0,
					default => $targetType->range->minLength->div($parameterType->numberRange->max->value)->ceil()
				},
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value :
					$targetType->range->maxLength->div($parameterType->numberRange->min->value)->ceil()
			);
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, IntegerValue $parameter): TupleValue {
			$splitLength = (int)(string)$parameter->literalValue;
			$result = mb_str_split($target->literalValue, $splitLength);
			return $this->valueRegistry->tuple(
				array_map(fn(string $piece): StringValue =>
				$this->valueRegistry->string($piece), $result)
			);
		};
	}

	protected function isParameterValueValid(Value $parameter, callable $executor): bool {
		if (!parent::isParameterValueValid($parameter, $executor)) {
			return false;
		}
		/** @var IntegerValue $parameter */
		return (int)(string)$parameter->literalValue >= 1;
	}

}
