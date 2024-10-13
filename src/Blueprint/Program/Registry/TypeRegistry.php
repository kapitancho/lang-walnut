<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
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
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;

interface TypeRegistry {
	public function any(): AnyType;
	public function nothing(): NothingType;

	public function null(): NullType;
	public function boolean(): BooleanType;
	public function true(): TrueType;
	public function false(): FalseType;

	public function integer(
		int|MinusInfinity $min = MinusInfinity::value,
		int|PlusInfinity $max = PlusInfinity::value
	): IntegerType;
	/** @param list<IntegerValue> $values */
	public function integerSubset(array $values): IntegerSubsetType;

	public function real(
		float|MinusInfinity $min = MinusInfinity::value,
		float|PlusInfinity $max = PlusInfinity::value
	): RealType;
	/** @param list<RealValue> $values */
	public function realSubset(array $values): RealSubsetType;

	public function string(
		int $minLength = 0,
		int|PlusInfinity $maxLength = PlusInfinity::value
	): StringType;
	/** @param list<StringValue> $values */
	public function stringSubset(array $values): StringSubsetType;

	public function array(
		Type             $itemType = null,
		int              $minLength = 0,
		int|PlusInfinity $maxLength = PlusInfinity::value
	): ArrayType;
	public function map(
		Type             $itemType = null,
		int              $minLength = 0,
		int|PlusInfinity $maxLength = PlusInfinity::value
	): MapType;
	/** @param list<Type> $itemTypes */
	public function tuple(array $itemTypes, Type $restType = null): TupleType;
	/** @param array<string, Type> $itemTypes */
	public function record(array $itemTypes, Type $restType = null): RecordType;

	/** @param list<Type> $types */
	public function union(array $types, bool $normalize = true): Type;
	/** @param list<Type> $types */
	public function intersection(array $types, bool $normalize = true): Type;

	public function function(Type $parameterType, Type $returnType): FunctionType;

	public function mutable(Type $valueType): MutableType;
	public function optionalKey(Type $valueType): OptionalKeyType;
	public function impure(Type $valueType): Type;
	public function result(Type $returnType, Type $errorType): Type;
	public function type(Type $refType): TypeType;

	public function proxyType(TypeNameIdentifier $typeName): ProxyNamedType;
	public function metaType(MetaTypeValue $value): MetaType;
	/** @throws UnknownType */
	public function typeByName(TypeNameIdentifier $typeName): Type;
	/** @throws UnknownType */
	public function withName(TypeNameIdentifier $typeName): NamedType;
	/** @throws UnknownType */
	public function alias(TypeNameIdentifier $typeName): AliasType;
	/** @throws UnknownType */
	public function subtype(TypeNameIdentifier $typeName): SubtypeType;
	/** @throws UnknownType */
	public function sealed(TypeNameIdentifier $typeName): SealedType;
	/** @throws UnknownType */
	public function atom(TypeNameIdentifier $typeName): AtomType;
	/** @throws UnknownType */
	public function enumeration(TypeNameIdentifier $typeName): EnumerationType;
}