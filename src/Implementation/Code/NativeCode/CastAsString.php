<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\BytesValue;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class CastAsString {


	/** @return list<string>|null */
	public function detectSubsetType(Type $targetType): array|null {
		return match(true) {
			$targetType instanceof AliasType => $this->detectSubsetType($targetType->aliasedType),
			$targetType instanceof MutableType => $this->detectSubsetType($targetType->valueType),
			$targetType instanceof NullType => ['null'],
			$targetType instanceof AtomType => [$targetType->name->identifier],
			$targetType instanceof TrueType => ['true'],
			$targetType instanceof FalseType => ['false'],
			$targetType instanceof BooleanType => ['true', 'false'],
			$targetType instanceof EnumerationSubsetType =>
				array_values(
					array_map(fn(EnumerationValue $enumerationValue): string => $enumerationValue->name->identifier, $targetType->subsetValues),
				),
			$targetType instanceof IntegerSubsetType =>
				array_values(
					array_map(fn(Number $integerValue): string => (string)$integerValue, $targetType->subsetValues),
				),
			$targetType instanceof RealSubsetType =>
				array_values(
					array_map(fn(Number $realValue): string => (string)$realValue, $targetType->subsetValues),
				),
			default => null
		};
	}

	/** @return array{int, int|PlusInfinity}|null */
	public function detectRangedType(Type $targetType): array|null {
		return match(true) {
			$targetType instanceof AliasType => $this->detectRangedType($targetType->aliasedType),
			$targetType instanceof MutableType => $this->detectRangedType($targetType->valueType),
			$targetType instanceof BytesType => [$targetType->range->minLength, $targetType->range->maxLength],
			$targetType instanceof IntegerType => [
				1,
				($max = $targetType->numberRange->max) instanceof NumberIntervalEndpoint &&
				($min = $targetType->numberRange->min) instanceof NumberIntervalEndpoint ?
					max(1,
						(int)ceil(log10(abs((int)(string)$max->value))),
						(int)ceil(log10(abs((int)(string)$min->value))) +
						($min->value < 0 ? 1 : 0)
					) : 1000
			],
			$targetType instanceof RealType => [1, 1000],
			$targetType instanceof TypeType => [1, PlusInfinity::value],
			default => null
		};
	}

	public function evaluate(Value $value): string|null {
		return match (true) {
			$value instanceof IntegerValue => (string)$value->literalValue,
			$value instanceof RealValue => (string) $value->literalValue,
			$value instanceof StringValue => $value->literalValue,
			$value instanceof BytesValue => $value->literalValue,
			$value instanceof BooleanValue => $value->literalValue ? 'true' : 'false',
			$value instanceof NullValue => 'null',
			$value instanceof TypeValue => (string)$value->typeValue,
			$value instanceof MutableValue => $this->evaluate($value->value),
			$value instanceof DataValue => $this->evaluate($value->value),
			$value instanceof AtomValue => $value->type->name,
			$value instanceof EnumerationValue => $value->name,
			default => null
		};
	}

}