<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ComplexTypeRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType as AtomTypeInterface;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Blueprint\Type\NamedType as NamedTypeInterface;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;

final class ComplexTypeStorage implements ComplexTypeRegistry {

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

	public function addAtom(TypeNameIdentifier $name, AtomType $type): AtomType {
		$this->atomTypes[$name->identifier] = $type;
		return $type;
	}

	public function addEnumerationType(EnumerationType $type): EnumerationType {
		$this->enumerationTypes[$type->name->identifier] = $type;
		return $type;
	}

	public function addAlias(TypeNameIdentifier $name, AliasType $type): AliasType {
		$this->aliasTypes[$name->identifier] = $type;
		return $type;
	}

	public function addData(TypeNameIdentifier $name, DataType $type): DataType {
		$this->dataTypes[$name->identifier] = $type;
		return $type;
	}

	public function addOpen(TypeNameIdentifier $name, OpenType $type): OpenType {
		$this->openTypes[$name->identifier] = $type;
		return $type;
	}

	public function addSealed(TypeNameIdentifier $name, SealedType $type): SealedType {
		$this->sealedTypes[$name->identifier] = $type;
		return $type;
	}


	/** @throws UnknownType */
	public function alias(TypeNameIdentifier $typeName): \Walnut\Lang\Implementation\Type\AliasType {
		return $this->aliasTypes[$typeName->identifier] ?? UnknownType::withName($typeName);
	}

	/** @throws UnknownType */
	public function data(TypeNameIdentifier $typeName): \Walnut\Lang\Implementation\Type\DataType {
		return $this->dataTypes[$typeName->identifier] ?? UnknownType::withName($typeName, 'data');
	}

	/** @throws UnknownType */
	public function open(TypeNameIdentifier $typeName): \Walnut\Lang\Implementation\Type\OpenType {
		return $this->openTypes[$typeName->identifier] ?? UnknownType::withName($typeName, 'open');
	}

	/** @throws UnknownType */
	public function sealed(TypeNameIdentifier $typeName): \Walnut\Lang\Implementation\Type\SealedType {
		return $this->sealedTypes[$typeName->identifier] ?? UnknownType::withName($typeName, 'sealed');
	}

	/** @throws UnknownType */
	public function atom(TypeNameIdentifier $typeName): AtomTypeInterface {
		return $this->atomTypes[$typeName->identifier] ?? UnknownType::withName($typeName, 'atom');
	}

	/** @throws UnknownType */
	public function enumeration(TypeNameIdentifier $typeName): EnumerationTypeInterface {
		return $this->enumerationTypes[$typeName->identifier] ?? UnknownType::withName($typeName, 'enumeration');
	}

	/**
	 * @param non-empty-list<EnumValueIdentifier> $values
	 * @throws UnknownEnumerationValue|DuplicateSubsetValue|InvalidArgumentException
	 **/
	public function enumerationSubsetType(TypeNameIdentifier $typeName, array $values): EnumerationSubsetType {
		return $this->enumeration($typeName)->subsetType($values);
	}

	public function withName(TypeNameIdentifier $typeName): NamedTypeInterface {
		return $this->atomTypes[$typeName->identifier] ??
			$this->enumerationTypes[$typeName->identifier] ??
			$this->aliasTypes[$typeName->identifier] ??
			$this->dataTypes[$typeName->identifier] ??
			$this->openTypes[$typeName->identifier] ??
			$this->sealedTypes[$typeName->identifier] ??
			UnknownType::withName($typeName);
	}

}