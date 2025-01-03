<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithoutLast implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof ArrayType) {
			$returnType = $this->context->typeRegistry->record([
				'element' => $targetType->itemType,
				'array' => $this->context->typeRegistry->array(
					$targetType->itemType,
					max(0, $targetType->range->minLength - 1),
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : $targetType->range->maxLength - 1
				)
			]);
			return $targetType->range->minLength > 0 ? $returnType :
				$this->context->typeRegistry->result($returnType,
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

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof TupleValue) {
			$values = $targetValue->values;
			if (count($values) === 0) {
				return TypedValue::forValue($this->context->valueRegistry->atom(
					new TypeNameIdentifier("ItemNotFound")
				));
			}
			$element = array_pop($values);
			return TypedValue::forValue($this->context->valueRegistry->record([
				'element' => $element,
				'array' => $this->context->valueRegistry->tuple($values)
			]));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}