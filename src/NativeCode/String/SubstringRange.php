<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class SubstringRange implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			$pInt = $this->context->typeRegistry->integer(0);
			$pType = $this->context->typeRegistry->record([
				"start" => $pInt,
				"end" => $pInt
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				$endType = $parameterType->types['end'];
				return $this->context->typeRegistry->string(0,
					$endType->range->maxValue === PlusInfinity::value ? PlusInfinity::value :
					min($targetType->range->maxLength,
					$endType->range->maxValue - $parameterType->types['start']->range->minValue
				));
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
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
		$parameterValue = $this->toBaseValue($parameterValue);
		if (
			$targetValue instanceof StringValue &&
			$parameterValue instanceof RecordValue
		) {
			$start = $parameterValue->valueOf('start');
			$end = $parameterValue->valueOf('end');
			if (
				$start instanceof IntegerValue &&
				$end instanceof IntegerValue
			) {
				$length = $end->literalValue - $start->literalValue;
				return TypedValue::forValue($this->context->valueRegistry->string(
					mb_substr(
						$targetValue->literalValue,
						$start->literalValue,
						$length
					)
				));
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