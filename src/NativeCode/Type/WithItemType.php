<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithItemType implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($parameterType->isSubtypeOf(
				$programRegistry->typeRegistry->type(
					$programRegistry->typeRegistry->any
				)
			)) {
				if ($refType instanceof ArrayType) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->array(
							$parameterType->refType,
							$refType->range->minLength,
							$refType->range->maxLength)
					);
				}
				if ($refType instanceof MapType) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->map(
							$parameterType->refType,
							$refType->range->minLength,
							$refType->range->maxLength
						)
					);
				}
				if ($refType instanceof SetType) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->set(
							$parameterType->refType,
							$refType->range->minLength,
							$refType->range->maxLength)
					);
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
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($parameter->type->isSubtypeOf(
				$programRegistry->typeRegistry->type(
					$programRegistry->typeRegistry->any
				)
			)) {
				if ($typeValue instanceof ArrayType) {
					$result = $programRegistry->typeRegistry->array(
						$parameter->value->typeValue,
						$typeValue->range->minLength,
						$typeValue->range->maxLength,
					);
					return TypedValue::forValue($programRegistry->valueRegistry->type($result));
				}
				if ($typeValue instanceof MapType) {
					$result = $programRegistry->typeRegistry->map(
						$parameter->value->typeValue,
						$typeValue->range->minLength,
						$typeValue->range->maxLength,
					);
					return TypedValue::forValue($programRegistry->valueRegistry->type($result));
				}
				if ($typeValue instanceof SetType) {
					$result = $programRegistry->typeRegistry->set(
						$parameter->value->typeValue,
						$typeValue->range->minLength,
						$typeValue->range->maxLength,
					);
					return TypedValue::forValue($programRegistry->valueRegistry->type($result));
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}