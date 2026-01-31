<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

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
		UserlandFunction|null $validator
	): OpenType;

	public function addSealed(
		TypeName $name,
		Type $valueType,
		UserlandFunction|null $validator
	): SealedType;
}