<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithLengthRange implements NativeMethod {

	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($parameterType->isSubtypeOf(
				$typeRegistry->withName(new TypeNameIdentifier('LengthRange'))
			)) {
				if ($refType instanceof StringType) {
					return $typeRegistry->type($typeRegistry->string());
				}
				if ($refType instanceof ArrayType) {
					return $typeRegistry->type($typeRegistry->array($refType->itemType));
				}
				if ($refType instanceof MapType) {
					return $typeRegistry->type($typeRegistry->map($refType->itemType));
				}
				if ($refType instanceof SetType) {
					return $typeRegistry->type($typeRegistry->set($refType->itemType));
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
				// @codeCoverageIgnoreEnd
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

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($parameter->type->isSubtypeOf(
				$programRegistry->typeRegistry->withName(new TypeNameIdentifier('LengthRange'))
			)) {
				if ($typeValue instanceof StringType) {
					$range = $parameter->value->values;
					$minValue = $range['minLength'];
					$maxValue = $range['maxLength'];
					$result = $programRegistry->typeRegistry->string(
						$minValue->literalValue,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return ($programRegistry->valueRegistry->type($result));
				}
				if ($typeValue instanceof ArrayType) {
					$range = $parameter->value->values;
					$minValue = $range['minLength'];
					$maxValue = $range['maxLength'];
					$result = $programRegistry->typeRegistry->array(
						$typeValue->itemType,
						$minValue->literalValue,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return ($programRegistry->valueRegistry->type($result));
				}
				if ($typeValue instanceof MapType) {
					$range = $parameter->value->values;
					$minValue = $range['minLength'];
					$maxValue = $range['maxLength'];
					$result = $programRegistry->typeRegistry->map(
						$typeValue->itemType,
						$minValue->literalValue,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return ($programRegistry->valueRegistry->type($result));
				}
				if ($typeValue instanceof SetType) {
					$range = $parameter->value->values;
					$minValue = $range['minLength'];
					$maxValue = $range['maxLength'];
					$result = $programRegistry->typeRegistry->set(
						$typeValue->itemType,
						$minValue->literalValue,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}