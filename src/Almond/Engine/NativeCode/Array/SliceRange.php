<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, RecordType, RecordValue> */
final readonly class SliceRange extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType, mixed $origin): null|string|ValidationFailure {
		$expectedType = $this->typeRegistry->record([
			"start" => $this->typeRegistry->integer(0),
			"end" => $this->typeRegistry->integer(0),
		], null);
		return $parameterType->isSubtypeOf($expectedType) ? null : sprintf(
			"Parameter type %s is not a subtype %s",
			$parameterType,
			$expectedType
		);
	}

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->record([
				"start" => $this->typeRegistry->integer(0),
				"end" => $this->typeRegistry->integer(0),
			], null)
		);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, RecordType $parameterType): Type {
			/** @var IntegerType $endType */
			$endType = $this->toBaseType($parameterType->types['end']);
			$maxLength = $endType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
				min(
					$targetType->range->maxLength,
					$parameterType->types['start']->numberRange->min === MinusInfinity::value ?
						// @codeCoverageIgnoreStart
						$targetType->range->maxLength :
						// @codeCoverageIgnoreEnd
						$endType->numberRange->max->value - $this->toBaseType($parameterType->types['start'])->numberRange->min->value
				);
			return $this->typeRegistry->array(
				$targetType->itemType,
				0,
				$maxLength
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, RecordValue $parameter): TupleValue {
			$start = $parameter->valueOf('start');
			$end = $parameter->valueOf('end');
			/** @var IntegerValue $start */
			/** @var IntegerValue $end */
			$length = $end->literalValue - $start->literalValue;
			return $this->valueRegistry->tuple(
				array_slice(
					$target->values,
					(int)(string)$start->literalValue,
					(int)(string)$length
				)
			);
		};
	}

}
