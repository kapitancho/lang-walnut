<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, RecordType, TupleValue, RecordValue> */
final readonly class SliceRange extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$pInt = $this->typeRegistry->integer(0);
			$pType = $this->typeRegistry->record([
				"start" => $pInt,
				"end" => $pInt
			], null);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof RecordType) {
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
				}
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
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
