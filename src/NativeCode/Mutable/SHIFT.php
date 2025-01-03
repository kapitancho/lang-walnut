<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class SHIFT implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType && $t->valueType instanceof ArrayType && (int)(string)$t->valueType->range->minLength === 0) {
			return $this->context->typeRegistry->result(
				$t->valueType->itemType,
				$this->context->typeRegistry->atom(new TypeNameIdentifier("ItemNotFound"))
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

		$v = $this->toBaseValue($targetValue);
		if ($v instanceof MutableValue) {
			$targetType = $this->toBaseType($v->targetType);
			if ($targetType instanceof ArrayType) {
				$values = $v->value->values;
				if (count($values) > 0) {
					$value = array_shift($values);
					$v->value = $this->context->valueRegistry->tuple($values);
					return new TypedValue($targetType->itemType, $value);
				}
				return TypedValue::forValue($this->context->valueRegistry->error(
					$this->context->valueRegistry->atom(new TypeNameIdentifier("ItemNotFound"))
				));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}