<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;

interface ComplexTypeRegistry {
	public function withName(TypeNameIdentifier $typeName): NamedType;
	/** @throws UnknownType */
	public function alias(TypeNameIdentifier $typeName): AliasType;
	/** @throws UnknownType */
	public function data(TypeNameIdentifier $typeName): DataType;
	/** @throws UnknownType */
	public function open(TypeNameIdentifier $typeName): OpenType;
	public function sealed(TypeNameIdentifier $typeName): SealedType;
	public function atom(TypeNameIdentifier $typeName): AtomType;
	/** @throws UnknownType */
	public function enumeration(TypeNameIdentifier $typeName): EnumerationType;
	/**
	  * @param non-empty-list<EnumValueIdentifier> $values
	  * @throws UnknownEnumerationValue|DuplicateSubsetValue|InvalidArgumentException
	  **/
    public function enumerationSubsetType(TypeNameIdentifier $typeName, array $values): EnumerationSubsetType;
}