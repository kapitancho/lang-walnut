<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Common\Range\NumberRange;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithNumberRange implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf(
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('IntegerNumberRange'))
				)) {
					return $programRegistry->typeRegistry->type($programRegistry->typeRegistry->integer());
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf(
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('RealNumberRange'))
				)) {
					return $programRegistry->typeRegistry->type($programRegistry->typeRegistry->real());
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
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

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof IntegerType || $typeValue instanceof RealType) {
				$prefix = $typeValue instanceof IntegerType ? 'Integer' : 'Real';
				if ($parameter->type->isSubtypeOf(
					$programRegistry->typeRegistry->withName(
						new TypeNameIdentifier($prefix . 'NumberRange')
					)
				)) {
					$intervals = [];
					foreach($parameter->value->values['intervals']->values as $interval) {
						['start' => $start, 'end' => $end] = $interval->value->values;
						$vStart = $start instanceof AtomValue ? MinusInfinity::value :
							new NumberIntervalEndpoint(
								$start->value->values['value']->literalValue,
								$start->value->values['inclusive']->literalValue
							);
						$vEnd = $end instanceof AtomValue ? PlusInfinity::value :
							new NumberIntervalEndpoint(
								$end->value->values['value']->literalValue,
								$end->value->values['inclusive']->literalValue
							);
						$intervals[] = new NumberInterval(
							$vStart,
							$vEnd
						);
					}
					$type = $typeValue instanceof IntegerType ?
						$programRegistry->typeRegistry->integerFull(...$intervals) :
						$programRegistry->typeRegistry->realFull(...$intervals
					);
					return $programRegistry->valueRegistry->type($type);
				}
			}
/*
			if ($typeValue instanceof IntegerType) {
				if ($parameter->type->isSubtypeOf(
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('IntegerRange'))
				)) {
					$range = $parameter->value->values;
					$minValue = $range['minValue'];
					$maxValue = $range['maxValue'];
					$result = $programRegistry->typeRegistry->integer(
						$minValue instanceof IntegerValue ? $minValue->literalValue : MinusInfinity::value,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
			if ($typeValue instanceof RealType) {
				if ($parameter->type->isSubtypeOf(
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('RealRange'))
				)) {
					$range = $parameter->value->values;
					$minValue = $range['minValue'];
					$maxValue = $range['maxValue'];
					$result = $programRegistry->typeRegistry->real(
						$minValue instanceof RealValue || $minValue instanceof IntegerValue ? $minValue->literalValue : MinusInfinity::value,
						$maxValue instanceof RealValue || $maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
*/
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}