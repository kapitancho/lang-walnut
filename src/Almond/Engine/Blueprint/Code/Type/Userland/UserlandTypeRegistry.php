<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

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