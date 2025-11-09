<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MergeWith implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$targetType = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if ($targetType instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			$parameterType = $parameterType instanceof RecordType ? $parameterType->asMapType() : $parameterType;
			if ($parameterType instanceof MapType) {
				return $typeRegistry->map(
					$typeRegistry->union([
						$targetType->itemType,
						$parameterType->itemType
					]),
					max($targetType->range->minLength, $parameterType->range->minLength),
					$targetType->range->maxLength === PlusInfinity::value ||
						$parameterType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : $targetType->range->maxLength + $parameterType->range->maxLength
				);
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
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof RecordValue) {
				$values = [... $targetValue->values, ... $parameterValue->values];
				return $programRegistry->valueRegistry->record($values);
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