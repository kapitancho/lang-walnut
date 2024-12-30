<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithReturnType implements NativeMethod {

	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType());
			if ($parameterType->isSubtypeOf(
				$this->context->typeRegistry()->type(
					$this->context->typeRegistry()->any()
				)
			)) {
				if ($refType instanceof ResultType) {
					return $this->context->typeRegistry()->type(
						$this->context->typeRegistry()->result(
							$parameterType->refType(),
							$refType->errorType(),
						)
					);
				} elseif ($refType instanceof FunctionType) {
					return $this->context->typeRegistry()->type(
						$this->context->typeRegistry()->function(
							$refType->parameterType(),
							$parameterType->refType()
						)
					);
				}
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue());
			if ($parameter->type->isSubtypeOf(
				$this->context->typeRegistry()->type(
					$this->context->typeRegistry()->any()
				)
			)) {
				if ($typeValue instanceof ResultType) {
					$result = $this->context->typeRegistry()->result(
						$parameter->value->typeValue(),
						$typeValue->errorType(),
					);
					return TypedValue::forValue($this->context->valueRegistry()->type($result));
				} elseif ($typeValue instanceof FunctionType) {
					$result = $this->context->typeRegistry()->function(
						$typeValue->parameterType(),
						$parameter->value->typeValue(),
					);
					return TypedValue::forValue($this->context->valueRegistry()->type($result));
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}