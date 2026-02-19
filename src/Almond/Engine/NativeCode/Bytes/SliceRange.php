<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, Type, BytesValue, Value> */
final readonly class SliceRange extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		$pInt = $this->typeRegistry->integer(0);
		$pType = $this->typeRegistry->record([
			"start" => $pInt,
			"end" => $pInt
		], null);
		return $parameterType->isSubtypeOf($pType) ?
			null :
			sprintf("Parameter type %s is not a subtype of [start: Integer<0..>, end: Integer<0..>]", $parameterType);
	}

	protected function getValidator(): callable {
		return function(BytesType $targetType, Type $parameterType): BytesType {
			/** @var RecordType $parameterType */
			$parameterType = $this->toBaseType($parameterType);
			$endType = $parameterType->types['end'];
			/** @var int|Number|PlusInfinity $maxLength */
			$maxLength = $endType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
				min(
					$targetType->range->maxLength,
					$endType->numberRange->max->value -
					$parameterType->types['start']->numberRange->min->value
				);
			return $this->typeRegistry->bytes(0, $maxLength);
		};
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, Value $parameter): BytesValue {
			/** @var RecordValue $parameter */
			$start = $parameter->valueOf('start');
			$end = $parameter->valueOf('end');
			/** @var IntegerValue $start */
			/** @var IntegerValue $end */
			$length = (int)(string)$end->literalValue - (int)(string)$start->literalValue;
			return $this->valueRegistry->bytes(
				substr(
					$target->literalValue,
					(int)(string)$start->literalValue,
					$length
				)
			);
		};
	}
}
