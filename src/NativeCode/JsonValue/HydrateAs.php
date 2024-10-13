<?php

namespace Walnut\Lang\NativeCode\JsonValue;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Code\NativeCode\HydrationException;
use Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

final readonly class HydrateAs implements NativeMethod {

	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry,
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($parameterType instanceof TypeType) {
			return $this->context->typeRegistry()->result(
				$parameterType->refType(),
				$this->context->typeRegistry()->withName(new TypeNameIdentifier("HydrationError"))
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
			try {
				return (new Hydrator(
					$this->context,
					$this->methodRegistry,
				))->hydrate($targetValue, $parameterValue->typeValue(), 'value');
			} catch (HydrationException $e) {
				return TypedValue::forValue($this->context->valueRegistry()->error(
					$this->context->valueRegistry()->sealedValue(
						new TypeNameIdentifier("HydrationError"),
						$this->context->valueRegistry()->record([
							'value' => $e->value,
							'hydrationPath' => $this->context->valueRegistry()->string($e->hydrationPath),
							'errorMessage' => $this->context->valueRegistry()->string($e->errorMessage),
						])
					)
				));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}