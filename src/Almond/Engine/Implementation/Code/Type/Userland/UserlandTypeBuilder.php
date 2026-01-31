<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeBuilder as UserlandTypeBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeFactory as UserlandTypeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeStorage as UserlandTypeStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

final readonly class UserlandTypeBuilder implements UserlandTypeBuilderInterface {

	public function __construct(
		private UserlandTypeFactoryInterface $userlandTypeFactory,
		private UserlandTypeStorageInterface $userlandTypeStorage
	) {}

	public function addAtom(TypeName $name): AtomType {
		return $this->userlandTypeStorage->addAtom($name,
			$this->userlandTypeFactory->atom($name)
		);
	}

	/** @param list<EnumerationValueName> $values */
	public function addEnumeration(TypeName $name, array $values): EnumerationType {
		return $this->userlandTypeStorage->addEnumeration(
			$name,
			$this->userlandTypeFactory->enumeration($name, $values)
		);
	}

	public function addAlias(TypeName $name, Type $aliasedType): AliasType {
		return $this->userlandTypeStorage->addAlias(
			$name,
			$this->userlandTypeFactory->alias($name, $aliasedType)
		);
	}

	public function addData(TypeName $name, Type $valueType): DataType {
		return $this->userlandTypeStorage->addData(
			$name,
			$this->userlandTypeFactory->data($name, $valueType)
		);
	}

	public function addOpen(
		TypeName $name,
		Type $valueType,
		UserlandFunction|null $validator
	): OpenType {
		return $this->userlandTypeStorage->addOpen(
			$name,
			$this->userlandTypeFactory->open($name, $valueType, $validator)
		);
	}

	public function addSealed(
		TypeName $name,
		Type $valueType,
		UserlandFunction|null $validator
	): SealedType {
		return $this->userlandTypeStorage->addSealed(
			$name,
			$this->userlandTypeFactory->sealed($name, $valueType, $validator)
		);
	}

}