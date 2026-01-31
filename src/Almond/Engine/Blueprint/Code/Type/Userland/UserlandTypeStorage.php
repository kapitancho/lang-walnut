<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\TypeAlreadyDefined;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface UserlandTypeStorage {

	/** @throws TypeAlreadyDefined */
	public function addAtom(TypeName $name, AtomType $type): AtomType;
	/** @throws TypeAlreadyDefined */
	public function addEnumeration(TypeName $name, EnumerationType $type): EnumerationType;
	/** @throws TypeAlreadyDefined */
	public function addAlias(TypeName $name, AliasType $type): AliasType;
	/** @throws TypeAlreadyDefined */
	public function addData(TypeName $name, DataType $type): DataType;
	/** @throws TypeAlreadyDefined */
	public function addOpen(TypeName $name, OpenType $type): OpenType;
	/** @throws TypeAlreadyDefined */
	public function addSealed(TypeName $name, SealedType $type): SealedType;
}