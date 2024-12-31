<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class BinaryNotEqual implements NativeMethod {

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): BooleanType {
		return $this->context->typeRegistry->boolean;
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		return TypedValue::forValue($this->context->valueRegistry->boolean(
            !$targetValue->equals($parameterValue)
        ));
	}

}