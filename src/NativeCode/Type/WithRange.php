<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithRange implements NativeMethod {

	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf(
					$typeRegistry->withName(new TypeNameIdentifier('IntegerRange'))
				)) {
					return $typeRegistry->type($typeRegistry->integer());
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf(
					$typeRegistry->withName(new TypeNameIdentifier('RealRange'))
				)) {
					return $typeRegistry->type($typeRegistry->real());
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
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof TypeValue) {
			$typeValue = $this->toBaseType($target->typeValue);
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
					return $programRegistry->valueRegistry->type($result);
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
					return $programRegistry->valueRegistry->type($result);
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}