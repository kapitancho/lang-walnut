<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface UserlandTypeFactory {
	public function atom(TypeName $name): AtomType;

	/** @param list<EnumerationValueName> $values */
	public function enumeration(TypeName $name, array $values): EnumerationType;
	public function alias(TypeName $name, Type $aliasedType): AliasType;
	public function data(TypeName $name, Type $valueType): DataType;
	public function open(TypeName $name, Type $valueType, UserlandFunction|null $validator): OpenType;
	public function sealed(TypeName $name, Type $valueType, UserlandFunction|null $validator): SealedType;
}