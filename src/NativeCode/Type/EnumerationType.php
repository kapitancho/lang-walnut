<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class EnumerationType implements NativeMethod {

	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $targetType->refType();
			if ($refType instanceof EnumerationSubsetType) {
				return $this->context->typeRegistry()->type($refType->enumeration());
			}
			if ($refType instanceof MetaType) {
				if ($refType->value() === MetaTypeValue::EnumerationSubset) {
					return $this->context->typeRegistry()->type($this->context->typeRegistry()->any());
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $targetValue->typeValue();
			if ($typeValue instanceof EnumerationSubsetType) {
				return TypedValue::forValue($this->context->valueRegistry()->type($typeValue->enumeration()));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}