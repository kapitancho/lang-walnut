<?php

namespace Walnut\Lang\NativeCode\Random;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\AtomValue;

final readonly class Integer implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof AtomType && $targetType->name->equals(new TypeNameIdentifier('Random'))) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof RecordType) {
				$fromType = $parameterType->types['min'] ?? null;
				$toType = $parameterType->types['max'] ?? null;

				if (
					($fromType instanceof IntegerType) &&
					($toType instanceof IntegerType)
				) {
					$fromMax = $fromType->numberRange->max;
					$toMin = $toType->numberRange->min;
					if (
						$fromMax === PlusInfinity::value ||
						$toMin === MinusInfinity::value ||
						$fromMax->value > $toMin->value
					) {
						// @codeCoverageIgnoreStart
						throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s - range is not compatible", __CLASS__, $parameterType));
						// @codeCoverageIgnoreEnd
					}
					return $typeRegistry->integerFull(
						new NumberInterval(
							$fromType->numberRange->min,
							$toType->numberRange->max
						)
					);
				}
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
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
			new TypeNameIdentifier('Random')
		)) {
			if ($parameter instanceof RecordValue) {
				$from = $parameter->valueOf('min');
				$to = $parameter->valueOf('max');
				if (
					$from instanceof IntegerValue &&
					$to instanceof IntegerValue
				) {
					/** @noinspection PhpUnhandledExceptionInspection */
					return $programRegistry->valueRegistry->integer(random_int(
						(int)(string)$from->literalValue,
						(int)(string)$to->literalValue
					));
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}