<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

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

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool|Type {
		if (!parent::isParameterTypeValid($parameterType, $validator, $targetType)) {
			return false;
		}
		$pInt = $this->typeRegistry->integer(0);
		return $parameterType->isSubtypeOf($this->typeRegistry->record([
			"start" => $pInt,
			"length" => $pInt
		], null));
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, RecordType $parameterType): StringType {
			return $this->typeRegistry->string(0, min(
				$targetType->range->maxLength,
				$parameterType->types['length']->numberRange->max->value
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
