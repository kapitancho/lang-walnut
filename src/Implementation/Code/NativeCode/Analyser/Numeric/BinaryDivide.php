<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Numeric;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

trait BinaryDivide {
	use BaseType;

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

			$real = $typeRegistry->real();
			//TODO: yyy - add +/-, -/+ and -/- cases as well as "inclusive" improvement
			if (
				$targetType->numberRange->min instanceof NumberIntervalEndpointInterface && $targetType->numberRange->min->value >= 0 &&
				$parameterType->numberRange->min instanceof NumberIntervalEndpointInterface && $parameterType->numberRange->min->value > 0
			) {
				$min = $parameterType->numberRange->max === PlusInfinity::value ?
					new NumberIntervalEndpoint(
						new Number(0),
						false
					):
					new NumberIntervalEndpoint(
						$targetType->numberRange->min->value->div($parameterType->numberRange->max->value),
						true
					);
				$max = $targetType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					new NumberIntervalEndpoint(
						$targetType->numberRange->max->value->div($parameterType->numberRange->min->value),
						true
					);
				$interval = new NumberInterval($min, $max);
				$real = $typeRegistry->realFull($interval);
			}
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
	                fdiv((string)$target->literalValue, (string)$parameter->literalValue)
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