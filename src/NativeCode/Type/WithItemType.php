<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithItemType implements NativeMethod {

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
				if ($refType instanceof ArrayType) {
					return $this->context->typeRegistry()->type(
						$this->context->typeRegistry()->array(
							$parameterType->refType(),
							$refType->range()->minLength(),
							$refType->range()->maxLength())
					);
				} elseif ($refType instanceof MapType) {
					return $this->context->typeRegistry()->type(
						$this->context->typeRegistry()->map(
							$parameterType->refType(),
							$refType->range()->minLength(),
							$refType->range()->maxLength()
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
				// @codeCoverageIgnoreEnd
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
				if ($typeValue instanceof ArrayType) {
					$result = $this->context->typeRegistry()->array(
						$parameter->value->typeValue(),
						$typeValue->range()->minLength(),
						$typeValue->range()->maxLength(),
					);
					return TypedValue::forValue($this->context->valueRegistry()->type($result));
				} elseif ($typeValue instanceof MapType) {
					$result = $this->context->typeRegistry()->map(
						$parameter->value->typeValue(),
						$typeValue->range()->minLength(),
						$typeValue->range()->maxLength(),
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