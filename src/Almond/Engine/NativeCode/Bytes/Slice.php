<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, Type, BytesValue, Value> */
final readonly class Slice extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		$pInt = $this->typeRegistry->integer(0);
		$pType = $this->typeRegistry->record([
			"start" => $pInt,
			"length" => $pInt
		], null);
		return $parameterType->isSubtypeOf($pType) ?
			null :
			sprintf("Parameter type %s is not a subtype of [start: Integer<0..>, length: Integer<0..>]", $parameterType);
	}

	protected function getValidator(): callable {
		return function(BytesType $targetType, Type $parameterType): BytesType {
			/** @var RecordType $parameterType */
			$parameterType = $this->toBaseType($parameterType);
			return $this->typeRegistry->bytes(0, min(
				$targetType->range->maxLength,
				$parameterType->types['length']->numberRange->max->value
			));
		};
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, Value $parameter): BytesValue {
			/** @var RecordValue $parameter */
			$start = $parameter->valueOf('start');
			$length = $parameter->valueOf('length');
			/** @var IntegerValue $start */
			/** @var IntegerValue $length */
			return $this->valueRegistry->bytes(
				substr(
					$target->literalValue,
					(int)(string)$start->literalValue,
					(int)(string)$length->literalValue
				)
			);
		};
	}
}
