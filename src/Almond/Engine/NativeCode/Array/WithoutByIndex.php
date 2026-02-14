<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, IntegerType, TupleValue, IntegerValue> */
final readonly class WithoutByIndex extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, IntegerType $parameterType, mixed $origin): Type|ValidationFailure {
			if ($targetType instanceof TupleType && $parameterType instanceof IntegerSubsetType && count($parameterType->subsetValues) === 1) {
				$param = (int)(string)$parameterType->subsetValues[0];
				if ($param >= 0) {
					$hasError = $param >= count($targetType->types);
					$paramType = $targetType->types[$param] ?? $targetType->restType;
					$paramItemTypes = $targetType->types;
					array_splice($paramItemTypes, $param, 1);
					$returnType = $paramType instanceof NothingType ?
						$paramType :
						$this->typeRegistry->record([
							'element' => $paramType,
							'array' => $hasError ? $targetType : $this->typeRegistry->tuple(
								$paramItemTypes,
								$targetType->restType
							)
						], null);
					return $hasError ?
						$this->typeRegistry->result(
							$returnType,
							$this->typeRegistry->core->indexOutOfRange
						) :
						$returnType;
				}
			}
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$returnType = $this->typeRegistry->record([
				'element' => $type->itemType,
				'array' => $this->typeRegistry->array(
					$type->itemType,
					max(0, $type->range->minLength - 1),
					$type->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : max($type->range->maxLength - 1, 0)
				)
			], null);
			return $parameterType->numberRange->min instanceof NumberIntervalEndpoint &&
				($parameterType->numberRange->min->value + ($parameterType->numberRange->min->inclusive ? 0 : 1)) >= 0 &&
				$parameterType->numberRange->max instanceof NumberIntervalEndpoint &&
				($parameterType->numberRange->max->value - ($parameterType->numberRange->max->inclusive ? 0 : 1)) <
					$type->range->maxLength ?
					$returnType :
					$this->typeRegistry->result(
						$returnType,
						$this->typeRegistry->core->indexOutOfRange
				);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, IntegerValue $parameter): Value {
			$values = $target->values;
			$p = (string)$parameter->literalValue;
			if (!array_key_exists($p, $values)) {
				return $this->valueRegistry->error(
					$this->valueRegistry->core->indexOutOfRange(
						$this->valueRegistry->record(['index' => $parameter])
					)
				);
			}
			$removed = array_splice($values, (int)$p, 1);
			return $this->valueRegistry->record([
				'element' => $removed[0],
				'array' => $this->valueRegistry->tuple($values)
			]);
		};
	}

}
