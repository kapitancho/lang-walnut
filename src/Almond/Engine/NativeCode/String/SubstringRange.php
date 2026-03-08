<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, Type, StringValue, Value> */
final readonly class SubstringRange extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		$pInt = $this->typeRegistry->integer(0);
		return $parameterType->isSubtypeOf($this->typeRegistry->record([
			"start" => $pInt,
			"end" => $pInt
		], null)) ?
			null : sprintf(
				"Expected parameter type to be [start: Integer<0..>, end: Integer<0..>], got %s.",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, RecordType $parameterType): StringType {
			/** @var IntegerType $startType */
			$startType = $parameterType->types['start'];
			/** @var IntegerType $endType */
			$endType = $parameterType->types['end'];
			/** @var int|Number|PlusInfinity $maxLength */
			$maxLength = $endType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
				min(
					$targetType->range->maxLength,
					$endType->numberRange->max->value -
					$startType->numberRange->min->value
				);
			return $this->typeRegistry->string(0, $maxLength);
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, Value $parameter): StringValue {
			/** @var RecordValue $parameter */
			/** @var IntegerValue $start */
			$start = $parameter->valueOf('start');
			/** @var IntegerValue $end */
			$end = $parameter->valueOf('end');
			$length = (int)(string)$end->literalValue - (int)(string)$start->literalValue;
			return $this->valueRegistry->string(
				mb_substr(
					$target->literalValue,
					(int)(string)$start->literalValue,
					$length
				)
			);
		};
	}
}
