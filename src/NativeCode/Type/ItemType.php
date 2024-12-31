<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class ItemType implements NativeMethod {

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
			if ($refType instanceof ArrayType || $refType instanceof MapType) {
				return $this->context->typeRegistry->type($refType->itemType);
			}
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
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof ArrayType || $typeValue instanceof MapType) {
				return TypedValue::forValue($this->context->valueRegistry->type($typeValue->itemType));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}