<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class MinValue implements NativeMethod {

	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType || $refType instanceof IntegerSubsetType) {
				return $this->context->typeRegistry->union([
					$this->context->typeRegistry->integer(),
					$this->context->typeRegistry->withName(new TypeNameIdentifier('MinusInfinity'))
				]);
			}
			if ($refType instanceof RealType || $refType instanceof RealSubsetType) {
				return $this->context->typeRegistry->union([
					$this->context->typeRegistry->real(),
					$this->context->typeRegistry->withName(new TypeNameIdentifier('MinusInfinity'))
				]);
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof IntegerType || $typeValue instanceof IntegerSubsetType) {
				return TypedValue::forValue($typeValue->range->minValue === MinusInfinity::value ?
					$this->context->valueRegistry->atom(new TypeNameIdentifier('MinusInfinity')) :
					$this->context->valueRegistry->integer($typeValue->range->minValue));
			}
			if ($typeValue instanceof RealType || $typeValue instanceof RealSubsetType) {
				return TypedValue::forValue($typeValue->range->minValue === MinusInfinity::value ?
					$this->context->valueRegistry->atom(new TypeNameIdentifier('MinusInfinity')) :
					$this->context->valueRegistry->real($typeValue->range->minValue));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}