<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait Partition {
	use BaseType;

	private function analyseHelper(
		TypeRegistry $typeRegistry,
		ArrayType|MapType|SetType $type,
		Type $parameterType,
		callable $typeBuilder
	): Type {
		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($typeRegistry->boolean)) {
			if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
				$partitionType = $typeBuilder($type->itemType, 0, $type->range->maxLength);
				return $typeRegistry->record([
					'matching' => $partitionType,
					'notMatching' => $partitionType
				]);
			}
			throw new AnalyserException(
				sprintf(
					"The parameter type %s of the callback function is not a subtype of %s",
					$type->itemType,
					$parameterType->parameterType
				)
			);
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	private function executeHelper(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter,
		callable $valueBuilder
	): Value {
		if ($parameter instanceof FunctionValue) {
			$values = $target->values;
			$matching = [];
			$notMatching = [];
			$true = $programRegistry->valueRegistry->true;

			foreach($values as $k => $value) {
				$r = $parameter->execute($programRegistry->executionContext, $value);
				if ($true->equals($r)) {
					$matching[$k] = $value;
				} else {
					$notMatching[$k] = $value;
				}
			}

			return $programRegistry->valueRegistry->record([
				'matching' => $valueBuilder($matching),
				'notMatching' => $valueBuilder($notMatching)
			]);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}