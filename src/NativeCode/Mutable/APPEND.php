<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class APPEND implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType) {
            $valueType = $this->toBaseType($t->valueType);
		    if ($valueType instanceof StringType && $valueType->range->maxLength === PlusInfinity::value) {
			    $p = $this->toBaseType($parameterType);
				if ($p instanceof StringType || $p instanceof StringSubsetType) {
					return $t;
				}
			    // @codeCoverageIgnoreStart
	            throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	            // @codeCoverageIgnoreEnd
            }
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
			$mv = $v->value;
			if ($targetType instanceof StringType && $mv instanceof StringValue) {
				$p = $this->toBaseValue($parameter->value);
				if ($p instanceof StringValue) {
					$v->value = $this->context->valueRegistry->string($mv->literalValue . $p->literalValue);
					return $target;
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value");
				// @codeCoverageIgnoreEnd

			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}