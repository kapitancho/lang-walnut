<?php

namespace Walnut\Lang\NativeCode\EventBus;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Fire implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof SealedType && $targetType->name()->equals(
			new TypeNameIdentifier('EventBus')
		)) {
			return $this->context->typeRegistry()->result(
				$parameterType,
				$this->context->typeRegistry()->withName(new TypeNameIdentifier('ExternalError'))
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
		if ($targetValue instanceof SealedValue && $targetValue->type()->name()->equals(
			new TypeNameIdentifier('EventBus')
		)) {
			$listeners = $targetValue->value()->values()['listeners'] ?? null;
			if ($listeners instanceof TupleValue) {
				foreach($listeners->values() as $listener) {
					if ($listener instanceof FunctionValue) {
						if ($parameterValue->type()->isSubtypeOf($listener->parameterType())) {
							$result = $listener->execute($this->context->globalContext(), $parameterValue);
							if ($result->type()->isSubtypeOf(
								$this->context->typeRegistry()->result(
									$this->context->typeRegistry()->nothing(),
									$this->context->typeRegistry()->withName(new TypeNameIdentifier('ExternalError'))
								)
							)) {
								return TypedValue::forValue($result);
							}
						}
					} else {
						throw new ExecutionException("Invalid listener");
					}
				}
				return TypedValue::forValue($parameterValue);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}