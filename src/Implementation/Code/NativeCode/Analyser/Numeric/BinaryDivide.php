<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Numeric;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

trait BinaryDivide {
	use BaseType;
	use RangeHelper;

	private function analyseHelper(
		TypeRegistry $typeRegistry,
		IntegerType|RealType $targetType,
		Type $parameterType,
	): Type {
		$parameterType = $this->toBaseType($parameterType);

		if ($parameterType instanceof IntegerType || $parameterType instanceof RealType) {
			if ($parameterType instanceof IntegerType && (string)$parameterType->numberRange === '1') {
				return $targetType;
			}
			$interval = $this->getDivideRange($targetType, $parameterType);
			$intervals = $this->getSplitInterval($interval, !$targetType->contains(0));
			$real = $typeRegistry->realFull(...$intervals);
			return $parameterType->contains(0) ?
				$typeRegistry->result(
					$real,
					$typeRegistry->atom(new TypeNameIdentifier('NotANumber'))
				) : $real;
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof RealValue || $target instanceof IntegerValue) {
			if ($parameter instanceof IntegerValue || $parameter instanceof RealValue) {
				if ((float)(string)$parameter->literalValue === 0.0) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					);
				}
				// Special case: Integer / 1 = Integer
				if (
					$parameter instanceof IntegerValue &&
					(string)$parameter->literalValue === '1'
				) {
					return $target;
				}
                return $programRegistry->valueRegistry->real(
	                fdiv((float)(string)$target->literalValue, (float)(string)$parameter->literalValue)
	                //$target->literalValue / $parameter->literalValue
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