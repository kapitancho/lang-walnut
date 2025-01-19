<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithReturnType implements NativeMethod {

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
				if ($refType instanceof ResultType) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->result(
							$parameterType->refType,
							$refType->errorType,
						)
					);
				}
				if ($refType instanceof FunctionType) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->function(
							$refType->parameterType,
							$parameterType->refType
						)
					);
				}
				if ($refType instanceof MetaType && $refType->value === MetaTypeValue::Function) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->function(
							$programRegistry->typeRegistry->nothing,
							$parameterType->refType
						)
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
				if ($typeValue instanceof ResultType) {
					$result = $programRegistry->typeRegistry->result(
						$parameter->value->typeValue,
						$typeValue->errorType,
					);
					return TypedValue::forValue($programRegistry->valueRegistry->type($result));
				}
				if ($typeValue instanceof FunctionType) {
					$result = $programRegistry->typeRegistry->function(
						$typeValue->parameterType,
						$parameter->value->typeValue,
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