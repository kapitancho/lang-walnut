<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\InvalidIntegerRange;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\InvalidRealRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
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
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;

interface TypeRegistry {
	public AnyType $any { get; }
	public NothingType $nothing { get; }

	public NullType $null { get; }
	public BooleanType $boolean { get; }
	public TrueType $true { get; }
	public FalseType $false { get; }

	/** @throws InvalidIntegerRange */
	public function integer(
		int|Number|MinusInfinity $min = MinusInfinity::value,
		int|Number|PlusInfinity $max = PlusInfinity::value
	): IntegerType;
	/** @param list<Number> $values */
	public function integerSubset(array $values): IntegerSubsetType;

	/** @throws InvalidRealRange */
	public function real(
		float|Number|MinusInfinity $min = MinusInfinity::value,
		float|Number|PlusInfinity $max = PlusInfinity::value
	): RealType;
	/** @param list<Number> $values */
	public function realSubset(array $values): RealSubsetType;

	/** @throws InvalidLengthRange */
	public function string(
		int|Number $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): StringType;
	/** @param list<string> $values */
	public function stringSubset(array $values): StringSubsetType;

	/** @throws InvalidLengthRange */
	public function array(
		Type|null        $itemType = null,
		int|Number              $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): ArrayType;

	/** @throws InvalidLengthRange */
	public function map(
		Type|null        $itemType = null,
		int|Number              $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): MapType;

	/** @param list<Type> $itemTypes */
	public function tuple(array $itemTypes, Type|null $restType = null): TupleType;
	/** @param array<string, Type> $itemTypes */
	public function record(array $itemTypes, Type|null $restType = null): RecordType;

	/** @param list<Type> $types */
	public function union(array $types, bool $normalize = true): Type;
	/** @param list<Type> $types */
	public function intersection(array $types, bool $normalize = true): Type;

	public function function(Type $parameterType, Type $returnType): FunctionType;

	public function mutable(Type $valueType): MutableType;
	public function optionalKey(Type $valueType): OptionalKeyType;
	public function impure(Type $valueType): Type;
	public function result(Type $returnType, Type $errorType): ResultType;
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