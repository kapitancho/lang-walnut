<?php

namespace Walnut\Lang\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
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

final readonly class BinaryDivide implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RealType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType || $parameterType instanceof RealType) {
				$real = $typeRegistry->real();
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
		

		if ($targetValue instanceof RealValue || $targetValue instanceof IntegerValue) {
			if ($parameterValue instanceof IntegerValue || $parameterValue instanceof RealValue) {
				if ((float)(string)$parameterValue->literalValue === 0.0) {
					return ($programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					));
				}
                return ($programRegistry->valueRegistry->real(
	                fdiv((string)$targetValue->literalValue, (string)$parameterValue->literalValue)
	                //$targetValue->literalValue / $parameter->literalValue
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