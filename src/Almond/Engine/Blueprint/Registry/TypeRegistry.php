<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Range\InvalidLengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Range\InvalidNumberRange;
use Walnut\Lang\Almond\Engine\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ShapeType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnknownType;

interface TypeRegistry extends TypeFinder {
	public AnyType $any { get; }
	public NothingType $nothing { get; }
	public NullType $null { get; }
	public BooleanType $boolean { get; }
	public FalseType $false { get; }
	public TrueType $true { get; }
	public function function(Type $parameterType, Type $returnType): FunctionType;
	public function mutable(Type $valueType): MutableType;
	public function shape(Type $refType): ShapeType;
	public function impure(Type $valueType): Type;
	public function result(Type $returnType, Type $errorType): ResultType;

	public function type(Type $targetType): TypeType;
	public function metaType(MetaTypeValue $value): MetaType;
	public function proxy(TypeName $typeName): AliasType;

	/** @param non-empty-list<Type> $types */
	public function union(array $types): Type;
	/** @param non-empty-list<Type> $types */
	public function intersection(array $types): Type;


	/** @throws InvalidLengthRange */
	public function string(
		int|Number $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): StringType;

	/** @throws InvalidLengthRange */
	public function bytes(
		int|Number $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): BytesType;

	public function integerFull(NumberInterval ... $intervals): IntegerType;
	public function nonZeroInteger(): IntegerType;

	/** @throws InvalidNumberRange */
	public function integer(
		int|Number|MinusInfinity $min = MinusInfinity::value,
		int|Number|PlusInfinity $max = PlusInfinity::value
	): IntegerType;

	/**
	 * @param list<Number> $values
	 * @throws InvalidArgument|DuplicateSubsetValue
	 */
	public function integerSubset(array $values): IntegerSubsetType;

	public function realFull(NumberInterval ... $intervals): RealType;
	public function nonZeroReal(): RealType;

	/** @throws InvalidNumberRange */
	public function real(
		float|Number|MinusInfinity $min = MinusInfinity::value,
		float|Number|PlusInfinity $max = PlusInfinity::value
	): RealType;

	/**
	 * @param list<Number> $values
	 * @throws InvalidArgument|DuplicateSubsetValue
	 */
	public function realSubset(array $values): RealSubsetType;

	/**
	 * @param list<string> $values
	 * @throws InvalidArgument|DuplicateSubsetValue
	 */
	public function stringSubset(array $values): StringSubsetType;

	/** @throws InvalidLengthRange */
	public function array(
		Type|null        $itemType = null,
		int|Number              $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): ArrayType;

	/** @throws InvalidLengthRange */
	public function map(
		Type|null               $itemType = null,
		int|Number              $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value,
		Type|null               $keyType = null,
	): MapType;

	/** @throws InvalidLengthRange */
	public function set(
		Type|null        $itemType = null,
		int|Number              $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): SetType;

	/** @param list<Type> $types */
	public function tuple(array $types, Type|null $restType): TupleType;

	/** @param array<string, Type> $types */
	public function record(array $types, Type|null $restType): RecordType;

	public UserlandTypeRegistry $userland { get; }

}