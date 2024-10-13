<?php

namespace Walnut\Lang\NativeCode\Clock;

use DateTimeImmutable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Now implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context,
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		if ($targetType instanceof AtomType && $targetType->name()->equals(
			new TypeNameIdentifier('Clock')
		)) {
			return $this->context->typeRegistry()->withName(new TypeNameIdentifier('DateAndTime'));
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
		if ($targetValue instanceof AtomValue && $targetValue->type()->name()->equals(
			new TypeNameIdentifier('Clock')
		)) {
			$now = new DateTimeImmutable;
			return TypedValue::forValue($this->context->valueRegistry()->subtypeValue(
				new TypeNameIdentifier('DateAndTime'),
				$this->context->valueRegistry()->record([
					'date' => $this->context->valueRegistry()->subtypeValue(
						new TypeNameIdentifier('Date'),
						$this->context->valueRegistry()->record([
							'year' => $this->context->valueRegistry()->integer((int)$now->format('Y')),
							'month' => $this->context->valueRegistry()->integer((int)$now->format('m')),
							'day' => $this->context->valueRegistry()->integer((int)$now->format('d')),
						])
					),
					'time' => $this->context->valueRegistry()->subtypeValue(
						new TypeNameIdentifier('Time'),
						$this->context->valueRegistry()->record([
							'hour' => $this->context->valueRegistry()->integer((int)$now->format('H')),
							'minute' => $this->context->valueRegistry()->integer((int)$now->format('i')),
							'second' => $this->context->valueRegistry()->integer((int)$now->format('s')),
						]
						)
					),
				])
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}