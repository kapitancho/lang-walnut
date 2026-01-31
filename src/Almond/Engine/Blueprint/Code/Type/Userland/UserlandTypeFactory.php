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

interface UserlandTypeFactory {
	public function atom(TypeName $name): AtomType;

	/** @param list<EnumerationValueName> $values */
	public function enumeration(TypeName $name, array $values): EnumerationType;
	public function alias(TypeName $name, Type $aliasedType): AliasType;
	public function data(TypeName $name, Type $valueType): DataType;
	public function open(TypeName $name, Type $valueType, UserlandFunction|null $validator): OpenType;
	public function sealed(TypeName $name, Type $valueType, UserlandFunction|null $validator): SealedType;
}