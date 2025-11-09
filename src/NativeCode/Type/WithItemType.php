<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithItemType implements NativeMethod {

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
				$typeRegistry->type(
					$typeRegistry->any
				)
			)) {
				if ($refType instanceof ArrayType) {
					return $typeRegistry->type(
						$typeRegistry->array(
							$parameterType->refType,
							$refType->range->minLength,
							$refType->range->maxLength)
					);
				}
				if ($refType instanceof MapType) {
					return $typeRegistry->type(
						$typeRegistry->map(
							$parameterType->refType,
							$refType->range->minLength,
							$refType->range->maxLength
						)
					);
				}
				if ($refType instanceof SetType) {
					return $typeRegistry->type(
						$typeRegistry->set(
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
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($parameter->type->isSubtypeOf(
				$programRegistry->typeRegistry->type(
					$programRegistry->typeRegistry->any
				)
			)) {
				if ($typeValue instanceof ArrayType) {
					$result = $programRegistry->typeRegistry->array(
						$parameter->typeValue,
						$typeValue->range->minLength,
						$typeValue->range->maxLength,
					);
					return $programRegistry->valueRegistry->type($result);
				}
				if ($typeValue instanceof MapType) {
					$result = $programRegistry->typeRegistry->map(
						$parameter->typeValue,
						$typeValue->range->minLength,
						$typeValue->range->maxLength,
					);
					return $programRegistry->valueRegistry->type($result);
				}
				if ($typeValue instanceof SetType) {
					$result = $programRegistry->typeRegistry->set(
						$parameter->typeValue,
						$typeValue->range->minLength,
						$typeValue->range->maxLength,
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