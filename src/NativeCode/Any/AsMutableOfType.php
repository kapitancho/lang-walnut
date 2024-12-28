<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class AsMutableOfType implements NativeMethod {
	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($parameterType instanceof TypeType) {
			return $this->context->typeRegistry()->result(
				//$this->context->typeRegistry()->type(
					$this->context->typeRegistry()->metaType(MetaTypeValue::MutableType)
				/*)*/,
				$this->context->typeRegistry()->sealed(new TypeNameIdentifier("CastNotAvailable"))
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		if ($parameterValue instanceof TypeValue) {
			if ($targetValue->type()->isSubtypeOf($parameterValue->typeValue())) {
				return TypedValue::forValue($this->context->valueRegistry()->mutable(
					$parameterValue->typeValue(),
					$targetValue
				));
			}
			return TypedValue::forValue($this->context->valueRegistry()->error(
				$this->context->valueRegistry()->sealedValue(
					new TypeNameIdentifier("CastNotAvailable"),
					$this->context->valueRegistry()->record([
						'from' => $this->context->valueRegistry()->type($targetValue->type()),
						'to' => $this->context->valueRegistry()->type($parameterValue->typeValue())
					])
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}