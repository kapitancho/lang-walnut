<?php

namespace Walnut\Lang\NativeCode\Bytes;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\BytesValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class SliceRange implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BytesType) {
			$pInt = $typeRegistry->integer(0);
			$pType = $typeRegistry->record([
				"start" => $pInt,
				"end" => $pInt
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				$endType = $parameterType->types['end'];
				/** @var int|Number|PlusInfinity $maxLength */
				$maxLength = $endType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					min(
						$targetType->range->maxLength,
						$endType->numberRange->max->value -
						$parameterType->types['start']->numberRange->min->value
					);
				return $typeRegistry->bytes(0, $maxLength);
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
        if (
			$target instanceof BytesValue &&
			$parameter instanceof RecordValue
		) {
			$start = $parameter->valueOf('start');
			$end = $parameter->valueOf('end');
			if (
            $start instanceof IntegerValue &&
            $end instanceof IntegerValue
			) {
				$length = (int)(string)$end->literalValue - (int)(string)$start->literalValue;
				return $programRegistry->valueRegistry->bytes(
					substr(
						$target->literalValue,
						(int)(string)$start->literalValue,
						$length
					)
				);
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
