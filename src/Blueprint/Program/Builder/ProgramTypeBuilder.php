<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;

interface ProgramTypeBuilder {
	public function addAtom(TypeNameIdentifier $name): AtomType;

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): EnumerationType;

	public function addAlias(TypeNameIdentifier $name, Type $aliasedType): AliasType;

	public function addSubtype(
		TypeNameIdentifier $name,
		Type $baseType,
		ExpressionNode $constructorBody,
		Type|null $errorType
	): SubtypeType;

	public function addSealed(
		TypeNameIdentifier $name,
		RecordType $valueType,
		ExpressionNode $constructorBody,
		Type|null $errorType
	): SealedType;
}