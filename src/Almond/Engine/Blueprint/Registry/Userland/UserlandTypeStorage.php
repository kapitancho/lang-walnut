<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Error\TypeAlreadyDefined;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;

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