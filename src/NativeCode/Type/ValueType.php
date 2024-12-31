<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\SealedType as SealedTypeInterface;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class ValueType implements NativeMethod {

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
			if ($refType instanceof SealedTypeInterface) {
				return $this->context->typeRegistry->type($refType->valueType);
			}
			if ($refType instanceof MetaType) {
				if ($refType->value === MetaTypeValue::Sealed) {
					return $this->context->typeRegistry->type($this->context->typeRegistry->any);
				}
				if ($refType->value === MetaTypeValue::MutableType) {
					return $this->context->typeRegistry->type($this->context->typeRegistry->any);
				}
			}
			if ($refType instanceof MutableType) {
				return $this->context->typeRegistry->type($refType->valueType);
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
			if ($typeValue instanceof SealedTypeInterface) {
				return TypedValue::forValue($this->context->valueRegistry->type($typeValue->valueType));
			}
			if ($typeValue instanceof MutableType) {
				return TypedValue::forValue($this->context->valueRegistry->type($typeValue->valueType));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}