<?php

namespace Walnut\Lang\NativeCode\Random;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\AtomValue;

final readonly class Integer implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
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
					($fromType instanceof IntegerType || $fromType instanceof IntegerSubsetType) &&
					($toType instanceof IntegerType || $toType instanceof IntegerSubsetType)
				) {
					$fromMax = $fromType->range->maxValue;
					$toMin = $toType->range->minValue;
					if ($fromMax === PlusInfinity::value || $toMin === MinusInfinity::value || $fromMax > $toMin) {
						// @codeCoverageIgnoreStart
						throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s - range is not compatible", __CLASS__, $parameterType));
						// @codeCoverageIgnoreEnd
					}
					return $programRegistry->typeRegistry->integer(
						$fromType->range->minValue,
						$toType->range->maxValue
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
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
				if ($targetValue instanceof AtomValue && $targetValue->type->name->equals(
			new TypeNameIdentifier('Random')
		)) {
			if ($parameterValue instanceof RecordValue) {
				$from = $parameterValue->valueOf('min');
				$to = $parameterValue->valueOf('max');
				if (
					$from instanceof IntegerValue &&
					$to instanceof IntegerValue
				) {
					/** @noinspection PhpUnhandledExceptionInspection */
					return TypedValue::forValue($programRegistry->valueRegistry->integer(random_int(
						(string)$from->literalValue,
						(string)$to->literalValue
					)));
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