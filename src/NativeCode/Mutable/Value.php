<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Value implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType) {
			return $t->valueType;
		}
		if ($t instanceof MetaType && $t->value === MetaTypeValue::MutableType) {
			return $this->context->typeRegistry->any;
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
			return new TypedValue($v->targetType, $v->value);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}