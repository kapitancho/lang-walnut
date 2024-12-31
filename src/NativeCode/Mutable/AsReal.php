<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsReal implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry,
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof MutableType) {
			$valueType = $targetType->valueType;
			$method = $this->methodRegistry->method(
				$valueType,
				new MethodNameIdentifier('asReal')
			);
			if ($method instanceof Method) {
				return $method->analyse($valueType, $parameterType);
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

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof MutableValue) {
			$value = $targetValue->value;
			$method = $this->methodRegistry->method(
				$targetValue->targetType,
				new MethodNameIdentifier('asReal')
			);
			if ($method instanceof Method) {
				return $method->execute(
					new TypedValue($targetValue->targetType, $value),
					$parameter);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}