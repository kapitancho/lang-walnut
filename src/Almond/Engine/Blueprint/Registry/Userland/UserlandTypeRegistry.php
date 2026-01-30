<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TrueType;

interface UserlandTypeRegistry {

	public NullType $null { get; }
	public BooleanType $boolean { get; }
	public TrueType $true { get; }
	public FalseType $false { get; }

	/** @return iterable<string, NamedType> */
	public function all(): iterable;
	/** @throws UnknownType */
	public function withName(TypeName $typeName): NamedType;
	/** @throws UnknownType */
	public function alias(TypeName $typeName): AliasType;
	/** @throws UnknownType */
	public function data(TypeName $typeName): DataType;
	/** @throws UnknownType */
	public function open(TypeName $typeName): OpenType;
	/** @throws UnknownType */
	public function sealed(TypeName $typeName): SealedType;
	/** @throws UnknownType */
	public function atom(TypeName $typeName): AtomType;
	/** @throws UnknownType */
	public function enumeration(TypeName $typeName): EnumerationType;
	/**
	 * @param non-empty-list<EnumerationValueName> $values *
	 * @throws UnknownType|UnknownEnumerationValue|DuplicateSubsetValue|InvalidArgument
	 */
    public function enumerationSubsetType(
		TypeName $typeName, array $values
    ): EnumerationSubsetType;
}