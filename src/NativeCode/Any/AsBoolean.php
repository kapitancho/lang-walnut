<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class AsBoolean implements NativeMethod {

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): BooleanType {
		return $this->context->typeRegistry()->boolean();
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		
        return TypedValue::forValue($this->context->valueRegistry()->boolean(
            $this->evaluate($targetValue)
        ));
	}

    private function evaluate(Value $value): bool {
        return match(true) {
            $value instanceof IntegerValue => $value->literalValue() !== 0,
            $value instanceof RealValue => $value->literalValue() !== 0.0,
            $value instanceof StringValue => $value->literalValue() !== '',
            $value instanceof BooleanValue => $value->literalValue(),
            $value instanceof NullValue => false,
            $value instanceof TupleValue => $value->values() !== [],
            $value instanceof RecordValue => $value->values() !== [],
            $value instanceof SubtypeValue => $this->evaluate($value->baseValue()),
            $value instanceof MutableValue => $this->evaluate($value->value()),
            //TODO: check for cast to boolean
            default => true
        };
    }

}