<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class SubstringRange implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			$pInt = $programRegistry->typeRegistry->integer(0);
			$pType = $programRegistry->typeRegistry->record([
				"start" => $pInt,
				"end" => $pInt
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				$endType = $parameterType->types['end'];
				return $programRegistry->typeRegistry->string(0,
					$endType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					min(
						$targetType->range->maxLength,
						$endType->numberRange->max->value -
						$parameterType->types['start']->numberRange->min->value
					)
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;

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
				$length = (string)$end->literalValue - (string)$start->literalValue;
				return ($programRegistry->valueRegistry->string(
					mb_substr(
						$targetValue->literalValue,
						(string)$start->literalValue,
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