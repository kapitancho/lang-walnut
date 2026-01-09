<?php

namespace Walnut\Lang\NativeCode\Clock;

use DateTimeImmutable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Now implements NativeMethod {
	use BaseType;

	public function __construct(
	) {}

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType
	): Type {
		if ($targetType instanceof AtomType && $targetType->name->equals(
			new TypeNameIdentifier('Clock')
		)) {
			return $typeRegistry->withName(new TypeNameIdentifier('DateAndTime'));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof AtomValue && $target->type->name->equals(
			new TypeNameIdentifier('Clock')
		)) {
			$now = new DateTimeImmutable;
			return $programRegistry->valueRegistry->openValue(
				new TypeNameIdentifier('DateAndTime'),
				$programRegistry->valueRegistry->record([
					'date' => $programRegistry->valueRegistry->openValue(
						new TypeNameIdentifier('Date'),
						$programRegistry->valueRegistry->record([
							'year' => $programRegistry->valueRegistry->integer((int)$now->format('Y')),
							'month' => $programRegistry->valueRegistry->integer((int)$now->format('m')),
							'day' => $programRegistry->valueRegistry->integer((int)$now->format('d')),
						])
					),
					'time' => $programRegistry->valueRegistry->openValue(
						new TypeNameIdentifier('Time'),
						$programRegistry->valueRegistry->record([
							'hour' => $programRegistry->valueRegistry->integer((int)$now->format('H')),
							'minute' => $programRegistry->valueRegistry->integer((int)$now->format('i')),
							'second' => $programRegistry->valueRegistry->integer((int)$now->format('s')),
						]
						)
					),
				])
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}