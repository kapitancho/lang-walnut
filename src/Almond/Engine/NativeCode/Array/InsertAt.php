<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, RecordType, RecordValue> */
final readonly class InsertAt extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->record([
				"value" => $this->typeRegistry->any,
				"index" => $this->typeRegistry->integer(0),
			], null)
		) ? null : "The parameter type should be a subtype of [value: Any, index: Integer<0..>]";
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, RecordType $parameterType): Type {
			$returnType = $this->typeRegistry->array(
				$this->typeRegistry->union([
					$targetType->itemType,
					$parameterType->types['value']
				]),
				$targetType->range->minLength + 1,
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $targetType->range->maxLength + 1
			);
			/** @var IntegerType $indexType */
			$indexType = $this->toBaseType($parameterType->types['index']);
			return $indexType->numberRange->max !== PlusInfinity::value &&
				$indexType->numberRange->max->value >= 0 &&
				$indexType->numberRange->max->value <= $targetType->range->minLength ?
				$returnType : $this->typeRegistry->result($returnType,
					$this->typeRegistry->core->indexOutOfRange
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
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		};
	}

}
