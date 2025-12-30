<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class CastAsBoolean {

	public function analyseType(BooleanType $boolean, TrueType $true, FalseType $false, Type $type): BooleanType|TrueType|FalseType {
		return match(true) {
			$type instanceof AliasType
			=> $this->analyseType($boolean, $true, $false, $type->aliasedType),
			$type instanceof MutableType
			=> $this->analyseType($boolean, $true, $false, $type->valueType),

			$type instanceof NullType,
				$type instanceof FalseType,
			($type instanceof IntegerSubsetType && count($type->subsetValues) === 1 && (int)(string)$type->subsetValues[0] === 0),
			($type instanceof IntegerType && $type->numberRange->min instanceof NumberIntervalEndpoint && $type->numberRange->max instanceof NumberIntervalEndpoint && (int)(string)$type->numberRange->min->value === 0 && (int)(string)$type->numberRange->max->value === 0),
			($type instanceof RealSubsetType && count($type->subsetValues) === 1 && (float)(string)$type->subsetValues[0] === 0.0),
			($type instanceof RealType && $type->numberRange->min instanceof NumberIntervalEndpoint && $type->numberRange->max instanceof NumberIntervalEndpoint && (float)(string)$type->numberRange->min->value === 0.0 && (float)(string)$type->numberRange->max->value === 0.0),
			($type instanceof StringSubsetType && count($type->subsetValues) === 1 && $type->subsetValues[0] === ''),
			($type instanceof StringType && $type->range->maxLength instanceof Number && (int)(string)$type->range->maxLength === 0),
			($type instanceof BytesType && $type->range->maxLength instanceof Number && (int)(string)$type->range->maxLength === 0),
			($type instanceof RecordType && count($type->types) === 0),
			($type instanceof TupleType && count($type->types) === 0),
			($type instanceof SetType && ($setLength = $type->range->maxLength) instanceof Number && ((int)(string)$setLength) === 0),
			($type instanceof ArrayType && $type->range->maxLength instanceof Number && (int)(string)$type->range->maxLength === 0),
			($type instanceof MapType && $type->range->maxLength instanceof Number && (int)(string)$type->range->maxLength === 0)
			=> $false,
			$type instanceof TrueType,
			($type instanceof IntegerSubsetType && !in_array(0, array_map(fn(Number $v) => (int)(string)$v, $type->subsetValues))),
			($type instanceof IntegerType && $type->numberRange->min !== MinusInfinity::value && $type->numberRange->min->value > 0),
			($type instanceof IntegerType && $type->numberRange->max !== PlusInfinity::value && $type->numberRange->max->value < 0),
			($type instanceof RealSubsetType && !in_array(0.0, array_map(fn(Number $v) => (float)(string)$v, $type->subsetValues))),
			($type instanceof RealType && $type->numberRange->min !== MinusInfinity::value && $type->numberRange->min->value > 0),
			($type instanceof RealType && $type->numberRange->max !== PlusInfinity::value && $type->numberRange->max->value < 0),
			($type instanceof StringSubsetType && !in_array('', array_map(fn(string $v) => $v, $type->subsetValues))),
			($type instanceof StringType && $type->range->minLength > 0),
			($type instanceof BytesType && $type->range->minLength > 0),
			=> $true,
			default => $boolean,
		};
	}


	public function evaluate(Value $value): bool {
		return match(true) {
			$value instanceof IntegerValue => (int)(string)$value->literalValue !== 0,
			$value instanceof RealValue => (float)(string)$value->literalValue !== 0.0,
			$value instanceof StringValue => $value->literalValue !== '',
			$value instanceof BooleanValue => $value->literalValue,
			$value instanceof NullValue => false,
			$value instanceof TupleValue => $value->values !== [],
			$value instanceof RecordValue => $value->values !== [],
			$value instanceof SetValue => $value->values !== [],
			$value instanceof MutableValue => $this->evaluate($value->value),
			default => true
		};
	}

}