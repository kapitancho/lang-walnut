<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;

interface TypeRegistryBuilder {
    public function addAtom(TypeNameIdentifier $name): AtomType;

    /** @param list<EnumValueIdentifier> $values */
    public function addEnumeration(TypeNameIdentifier $name, array $values): EnumerationType;

	public function addAlias(TypeNameIdentifier $name, Type $aliasedType): AliasType;

	public function addOpen(
		TypeNameIdentifier $name,
		Type $valueType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): OpenType;

	public function addSealed(
		TypeNameIdentifier $name,
		Type $valueType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): SealedType;
}