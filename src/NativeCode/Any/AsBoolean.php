<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\TupleType;
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
	): BooleanType|TrueType|FalseType {
		return $this->analyseType($targetType);
	}

	private function analyseType(Type $type): BooleanType|TrueType|FalseType {
		return match(true) {
			$type instanceof AliasType
				=> $this->analyseType($type->aliasedType),
			$type instanceof SubtypeType
				=> $this->analyseType($type->baseType),
			$type instanceof MutableType
				=> $this->analyseType($type->valueType),

			$type instanceof NullType,
			$type instanceof FalseType,
			($type instanceof IntegerSubsetType && count($type->subsetValues) === 1 && $type->subsetValues[0]->literalValue === 0),
			($type instanceof IntegerType && $type->range->minValue === 0 && $type->range->maxValue === 0),
			($type instanceof RealSubsetType && count($type->subsetValues) === 1 && (float)$type->subsetValues[0]->literalValue === 0.0),
			($type instanceof RealType && $type->range->minValue === 0.0 && $type->range->maxValue === 0.0),
			($type instanceof StringSubsetType && count($type->subsetValues) === 1 && $type->subsetValues[0]->literalValue === ''),
			($type instanceof StringType && $type->range->maxLength === 0),
			($type instanceof RecordType && count($type->types) === 0),
			($type instanceof TupleType && count($type->types) === 0),
			($type instanceof ArrayType && $type->range->maxLength === 0),
			($type instanceof MapType && $type->range->maxLength === 0)
				=> $this->context->typeRegistry->false,
			$type instanceof SealedType,
			$type instanceof TrueType,
			($type instanceof IntegerSubsetType && !in_array(0, array_map(fn(IntegerValue $v) => $v->literalValue, $type->subsetValues))),
			($type instanceof IntegerType && $type->range->minValue !== MinusInfinity::value && $type->range->minValue > 0),
			($type instanceof IntegerType && $type->range->maxValue !== PlusInfinity::value && $type->range->maxValue < 0),
			($type instanceof RealSubsetType && !in_array(0.0, array_map(fn(IntegerValue|RealValue $v) => $v->literalValue, $type->subsetValues))),
			($type instanceof RealType && $type->range->minValue !== MinusInfinity::value && $type->range->minValue > 0),
			($type instanceof RealType && $type->range->maxValue !== PlusInfinity::value && $type->range->maxValue < 0),
			($type instanceof StringSubsetType && !in_array('', array_map(fn(StringValue $v) => $v->literalValue, $type->subsetValues))),
			($type instanceof StringType && $type->range->minLength > 0),
			($type instanceof RecordType && count($type->types) > 0),
			($type instanceof TupleType && count($type->types) > 0),
			($type instanceof ArrayType && $type->range->minLength > 0),
			($type instanceof MapType && $type->range->minLength > 0)
				=> $this->context->typeRegistry->true,
			default => $this->context->typeRegistry->boolean,
			/*$type instanceof IntegerType,
			$type instanceof IntegerSubsetType,
			$type instanceof RealType,
			$type instanceof RealSubsetType,
			$type instanceof StringType,
			$type instanceof StringSubsetType,
			$type instanceof BooleanValue,
			$type instanceof TupleType, $type instanceof ArrayType,
			$type instanceof RecordType, $type instanceof MapType
				=> $this->context->typeRegistry()->boolean(),*/
		};
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		
        return TypedValue::forValue($this->context->valueRegistry->boolean(
            $this->evaluate($targetValue)
        ));
	}

    private function evaluate(Value $value): bool {
        return match(true) {
            $value instanceof IntegerValue => $value->literalValue !== 0,
            $value instanceof RealValue => $value->literalValue !== 0.0,
            $value instanceof StringValue => $value->literalValue !== '',
            $value instanceof BooleanValue => $value->literalValue,
            $value instanceof NullValue => false,
            $value instanceof TupleValue => $value->values !== [],
            $value instanceof RecordValue => $value->values !== [],
            $value instanceof SubtypeValue => $this->evaluate($value->baseValue),
            $value instanceof MutableValue => $this->evaluate($value->value),
            //TODO: check for cast to boolean
            default => true
        };
    }

}