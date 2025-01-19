<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MaxValue implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType || $refType instanceof IntegerSubsetType) {
				return $programRegistry->typeRegistry->union([
					$programRegistry->typeRegistry->integer(),
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('PlusInfinity'))
				]);
			}
			if ($refType instanceof RealType || $refType instanceof RealSubsetType) {
				return $programRegistry->typeRegistry->union([
					$programRegistry->typeRegistry->real(),
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier('PlusInfinity'))
				]);
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof IntegerType || $typeValue instanceof IntegerSubsetType) {
				return TypedValue::forValue($typeValue->range->maxValue === PlusInfinity::value ?
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier('PlusInfinity')) :
					$programRegistry->valueRegistry->integer($typeValue->range->maxValue)
				);
			}
			if ($typeValue instanceof RealType || $typeValue instanceof RealSubsetType) {
				return TypedValue::forValue($typeValue->range->maxValue === PlusInfinity::value ?
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier('PlusInfinity')) :
					$programRegistry->valueRegistry->real($typeValue->range->maxValue)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}