<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, RecordType, TupleValue, RecordValue> */
final readonly class InsertAt extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$pInt = $this->typeRegistry->integer(0);
			$pType = $this->typeRegistry->record([
				"value" => $this->typeRegistry->any,
				"index" => $pInt
			], null);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof RecordType) {
					$returnType = $this->typeRegistry->array(
						$this->typeRegistry->union([
							$targetType->itemType,
							$parameterType->types['value']
						]),
						$targetType->range->minLength + 1,
						$targetType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : $targetType->range->maxLength + 1
					);
					$indexType = $this->toBaseType($parameterType->types['index']);
					return $indexType->numberRange->max !== PlusInfinity::value &&
						$indexType->numberRange->max->value >= 0 &&
						$indexType->numberRange->max->value <= $targetType->range->minLength ?
						$returnType : $this->typeRegistry->result($returnType,
							$this->typeRegistry->core->indexOutOfRange
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
		return function(TupleValue $target, RecordValue $parameter): Value {
			$value = $parameter->valueOf('value');
			$index = $parameter->valueOf('index');
			if ($index instanceof IntegerValue) {
				$idx = (int)(string)$index->literalValue;
				$values = $target->values;
				if ($idx >= 0 && $idx <= count($values)) {
					array_splice($values, $idx, 0, [$value]);
					return $this->valueRegistry->tuple($values);
				}
				return $this->valueRegistry->error(
					$this->valueRegistry->core->indexOutOfRange(
						$this->valueRegistry->record([
							'index' => $index
						])
					)
				);
			}
			// @codeCoverageIgnoreStart
			throw new \Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		};
	}

}
