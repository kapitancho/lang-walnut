<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType as FalseTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType as NullTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType as TrueTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\TypeAlreadyDefined;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeRegistry as UserlandTypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeStorage as UserlandTypeStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\NullType;

final class UserlandTypeStorage implements UserlandTypeRegistryInterface, UserlandTypeStorageInterface {

	public readonly NullTypeInterface $null;
	public readonly BooleanTypeInterface $boolean;
	public readonly TrueTypeInterface $true;
	public readonly FalseTypeInterface $false;

	/** @var array<string, NamedType> */
	private array $allTypes = [];

	/** @var array<string, AtomType> */
	private array $atomTypes = [];
	/** @var array<string, EnumerationType> */
	private array $enumerationTypes = [];
	/** @var array<string, AliasType> */
	private array $aliasTypes = [];
	/** @var array<string, DataType> */
	private array $dataTypes = [];
	/** @var array<string, OpenType> */
	private array $openTypes = [];
	/** @var array<string, SealedType> */
	private array $sealedTypes = [];

	public function __construct() {
		$this->addNull();
		$this->addBoolean();
	}

	private function addNull(): void {
		$nullName = new TypeName('Null');
		$this->null = new NullType($nullName);
		$this->addAtom($nullName, $this->null);
	}

	private function addBoolean(): void {
		$booleanName = new TypeName('Boolean');
		$this->boolean = new BooleanType($booleanName);
		$this->true = $this->boolean->trueType;
		$this->false = $this->boolean->falseType;
		$this->addEnumeration($booleanName, $this->boolean);
	}

	/** @throws UnknownType */
	public function withName(TypeName $typeName): NamedType {
		return $this->allTypes[$typeName->identifier] ?? UnknownType::of($typeName);
	}

	/** @return iterable<string, NamedType> */
	public function all(): iterable {
		yield from $this->allTypes;
	}

	/** @throws UnknownType */
	public function alias(TypeName $typeName): AliasType {
		return $this->aliasTypes[$typeName->identifier] ?? UnknownType::of($typeName);
	}
	/** @throws UnknownType */
	public function data(TypeName $typeName): DataType {
		return $this->dataTypes[$typeName->identifier] ?? UnknownType::of($typeName);
	}
	/** @throws UnknownType */
	public function open(TypeName $typeName): OpenType {
		return $this->openTypes[$typeName->identifier] ?? UnknownType::of($typeName);
	}
	/** @throws UnknownType */
	public function sealed(TypeName $typeName): SealedType {
		return $this->sealedTypes[$typeName->identifier] ?? UnknownType::of($typeName);
	}
	/** @throws UnknownType */
	public function atom(TypeName $typeName): AtomType {
		return $this->atomTypes[$typeName->identifier] ?? UnknownType::of($typeName);
	}
	/** @throws UnknownType */
	public function enumeration(TypeName $typeName): EnumerationType {
		return $this->enumerationTypes[$typeName->identifier] ?? UnknownType::of($typeName);
	}
	/**
	 * @param non-empty-list<EnumerationValueName> $values *
	 * @throws UnknownType|UnknownEnumerationValue|DuplicateSubsetValue|InvalidArgument
	 */
    public function enumerationSubsetType(
		TypeName $typeName, array $values
    ): EnumerationSubsetType {
		return $this->enumeration($typeName)->subsetType($values);
    }

	/** @throws TypeAlreadyDefined */
	public function addType(TypeName $name, NamedType $type): void {
		if (array_key_exists($name->identifier, $this->allTypes)) {
			TypeAlreadyDefined::of($name);
		}
		$this->allTypes[$name->identifier] = $type;
	}

	/** @throws TypeAlreadyDefined */
	public function addAtom(TypeName $name, AtomType $type): AtomType {
		$this->addType($name, $type);
		$this->atomTypes[$name->identifier] = $type;
		return $type;
	}

	/** @throws TypeAlreadyDefined */
	public function addEnumeration(TypeName $name, EnumerationType $type): EnumerationType {
		$this->addType($name, $type);
		$this->enumerationTypes[$name->identifier] = $type;
		return $type;
	}

	/** @throws TypeAlreadyDefined */
	public function addAlias(TypeName $name, AliasType $type): AliasType {
		$this->addType($name, $type);
		$this->aliasTypes[$name->identifier] = $type;
		return $type;
	}

	/** @throws TypeAlreadyDefined */
	public function addData(TypeName $name, DataType $type): DataType {
		$this->addType($name, $type);
		$this->dataTypes[$name->identifier] = $type;
		return $type;
	}

	/** @throws TypeAlreadyDefined */
	public function addOpen(TypeName $name, OpenType $type): OpenType {
		$this->addType($name, $type);
		$this->openTypes[$name->identifier] = $type;
		return $type;
	}

	/** @throws TypeAlreadyDefined */
	public function addSealed(TypeName $name, SealedType $type): SealedType {
		$this->addType($name, $type);
		$this->sealedTypes[$name->identifier] = $type;
		return $type;
	}

}