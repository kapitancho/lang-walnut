<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface UserlandTypeBuilder {
    public function addAtom(TypeName $name): AtomType;

    /** @param list<EnumerationValueName> $values */
    public function addEnumeration(TypeName $name, array $values): EnumerationType;

	public function addAlias(TypeName $name, Type $aliasedType): AliasType;

	public function addData(
		TypeName $name,
		Type $valueType
	): DataType;

	public function addOpen(
		TypeName $name,
		Type $valueType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): OpenType;

	public function addSealed(
		TypeName $name,
		Type $valueType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): SealedType;
}