<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeBuilder as UserlandTypeBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeFactory as UserlandTypeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeStorage as UserlandTypeStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

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
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): OpenType {
		return $this->userlandTypeStorage->addOpen(
			$name,
			$this->userlandTypeFactory->open($name, $valueType)
		);
	}

	public function addSealed(
		TypeName $name,
		Type $valueType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): SealedType {
		return $this->userlandTypeStorage->addSealed(
			$name,
			$this->userlandTypeFactory->sealed($name, $valueType)
		);
	}

}