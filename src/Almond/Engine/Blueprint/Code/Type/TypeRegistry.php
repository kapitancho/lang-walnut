<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ShapeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\InvalidNumberRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;

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

	public TypeRegistryCore $core { get; }

}