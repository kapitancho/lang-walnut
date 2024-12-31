<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Without implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
        $targetType = $this->toBaseType($targetType);
        $type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$returnType = $this->context->typeRegistry->array(
				$type->itemType,
				max(0, $type->range->minLength - 1),
				$type->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $type->range->maxLength - 1
			);
			return $this->context->typeRegistry->result(
				$returnType,
				$this->context->typeRegistry->atom(
					new TypeNameIdentifier("ItemNotFound")
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof TupleValue) {
			$values = $targetValue->values;
			foreach($values as $index => $value) {
				if ($value->equals($parameterValue)) {
					array_splice($values, $index, 1);
					return new TypedValue(
						$this->context->typeRegistry->result(
							$value->type,
							$this->context->typeRegistry->atom(new TypeNameIdentifier('ItemNotFound'))
						),
						$this->context->valueRegistry->tuple($values)
					);
				}
			}
			return TypedValue::forValue($this->context->valueRegistry->error(
				$this->context->valueRegistry->atom(new TypeNameIdentifier("ItemNotFound"))
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}