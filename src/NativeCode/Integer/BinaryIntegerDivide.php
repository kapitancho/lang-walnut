<?php

namespace Walnut\Lang\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class BinaryIntegerDivide implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType) {
				if ((string)$parameterType->numberRange === '1') {
					return $targetType;
				}

				$int = $typeRegistry->integer();
				if (
					$targetType->numberRange->min instanceof NumberIntervalEndpointInterface && $targetType->numberRange->min->value >= 0 &&
					$parameterType->numberRange->min instanceof NumberIntervalEndpointInterface && $parameterType->numberRange->min->value >= 0
				) {
					$pMin = match(true) {
						$parameterType->numberRange->min === MinusInfinity::value => MinusInfinity::value,
						(string)$parameterType->numberRange->min->value === '0' => new Number(1),
						default => $parameterType->numberRange->min->value
					};
					$pMax = match(true) {
						$parameterType->numberRange->max === PlusInfinity::value => PlusInfinity::value,
						(string)$parameterType->numberRange->max->value === '0' => new Number(-1),
						default => $parameterType->numberRange->max->value
					};
					$min = $parameterType->numberRange->max === PlusInfinity::value ?
						new NumberIntervalEndpoint(
							new Number(0),
							true
						) :
						new NumberIntervalEndpoint(
							$targetType->numberRange->min->value->div($pMax)->floor(),
							true
						);
					$max = $targetType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
						new NumberIntervalEndpoint(
							$targetType->numberRange->max->value->div($pMin)->floor(),
							true
						);
					$interval = new NumberInterval($min, $max);
					$int = $typeRegistry->integerFull($interval);
				}

				return $parameterType->contains(0) ?
					$typeRegistry->result(
						$int,
						$typeRegistry->atom(new TypeNameIdentifier('NotANumber'))
					) : $int;
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
		if ($target instanceof IntegerValue) {
			if ($parameter instanceof IntegerValue) {
				if ((int)(string)$parameter->literalValue === 0) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					);
				}
                return $programRegistry->valueRegistry->integer(
	                intdiv((int)(string)$target->literalValue, (int)(string)$parameter->literalValue)
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