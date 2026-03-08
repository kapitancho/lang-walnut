<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, Type, StringValue, Value> */
final readonly class Substring extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		$pInt = $this->typeRegistry->integer(0);
		return $parameterType->isSubtypeOf($this->typeRegistry->record([
			"start" => $pInt,
			"length" => $pInt
		], null)) ?
			null : sprintf(
				"Expected parameter type to be [start: Integer<0..>, length: Integer<0..>], got %s.",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, RecordType $parameterType): StringType {
			/** @var IntegerType $lengthType */
			$lengthType = $parameterType->types['length'];
			return $this->typeRegistry->string(0, min(
				$targetType->range->maxLength,
				$lengthType->numberRange->max->value
			));
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, Value $parameter): StringValue {
			/** @var RecordValue $parameter */
			$start = $parameter->valueOf('start');
			$length = $parameter->valueOf('length');
			/** @var IntegerValue $start */
			/** @var IntegerValue $length */
			return $this->valueRegistry->string(
				mb_substr(
					$target->literalValue,
					(int)(string)$start->literalValue,
					(int)(string)$length->literalValue
				)
			);
		};
	}
}
