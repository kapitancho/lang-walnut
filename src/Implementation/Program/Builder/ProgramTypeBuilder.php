<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\ProgramTypeBuilder as ProgramTypeBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class ProgramTypeBuilder implements ProgramTypeBuilderInterface {

	public function __construct(
		public TypeRegistry                $typeRegistry,
		public TypeRegistryBuilder         $typeRegistryBuilder,
		public ValueRegistry               $valueRegistry,
		public ExpressionRegistry          $expressionRegistry,
		public CustomMethodRegistryBuilder $customMethodRegistryBuilder,
	) {}

	public function addAtom(TypeNameIdentifier $name): AtomType {
		return $this->typeRegistryBuilder->addAtom($name);
	}

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): EnumerationType {
		return $this->typeRegistryBuilder->addEnumeration($name, $values);
	}

	public function addAlias(TypeNameIdentifier $name, Type $aliasedType): AliasType {
		return $this->typeRegistryBuilder->addAlias($name, $aliasedType);
	}

	public function addSubtype(
		TypeNameIdentifier $name,
		Type $baseType,
		Expression $constructorBody,
		Type|null $errorType
	): SubtypeType {
		$subtype = $this->typeRegistryBuilder->addSubtype($name, $baseType);

		$this->addConstructorMethod($name, $baseType, $errorType, $constructorBody);

		return $subtype;
	}

	public function addSealed(
		TypeNameIdentifier $name,
		RecordType $valueType,
		Expression $constructorBody,
		Type|null $errorType
	): SealedType {
		$sealedType = $this->typeRegistryBuilder->addSealed($name, $valueType);

		$this->addConstructorMethod($name, $valueType, $errorType, $constructorBody);
		return $sealedType;
	}

	public function addConstructorMethod(
		TypeNameIdentifier $name,
		Type $fromType,
		Type|null $errorType,
		Expression $constructorBody
	): void {
		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->atom(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier('as' . $name->identifier),
			$fromType,
			$this->typeRegistry->nothing,
			$errorType && !($errorType instanceof NothingType) ?
				$this->typeRegistry->result($fromType, $errorType) :
				$fromType,
			$this->expressionRegistry->functionBody(
				$constructorBody,
			)
		);
	}
}