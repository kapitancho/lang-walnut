<?php

namespace Walnut\Lang\Implementation\Program\Builder;


use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\Builder\ComplexTypeBuilder;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder as TypeRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Value\AtomValue;
use Walnut\Lang\Implementation\Value\EnumerationValue;

final readonly class TypeRegistryBuilder implements TypeRegistryBuilderInterface {

	public function __construct(
		private TypeRegistry                $typeRegistry,
		private CustomMethodRegistryBuilder $customMethodRegistryBuilder,
		private ComplexTypeBuilder          $complexTypeBuilder,
		private ComplexTypeStorage          $complexTypeStorage,
	) {
		$null = $typeRegistry->null;
		$this->complexTypeStorage->addAtom($null->name, $null);
		$constructor = $typeRegistry->constructor;
		$this->complexTypeStorage->addAtom($constructor->name, $constructor);
		$boolean = $typeRegistry->boolean;
		$this->complexTypeStorage->addEnumerationType($boolean);

		$jsonValueTypeName = CoreType::JsonValue->typeName();
		$jsonValueTypeProxy = $typeRegistry->proxyType($jsonValueTypeName);
		$jsonValue = $typeRegistry->union([
			$typeRegistry->string(),
			$typeRegistry->real(),
			$boolean,
			$null,
			$typeRegistry->array($jsonValueTypeProxy),
			$typeRegistry->map($jsonValueTypeProxy),
			$typeRegistry->set($jsonValueTypeProxy),
			$typeRegistry->mutable($jsonValueTypeProxy),
		]);
		$this->complexTypeStorage->addAlias(
			$jsonValueTypeName,
			$this->complexTypeBuilder->alias($jsonValueTypeName, $jsonValue)
		);
	}

	public function addAtom(TypeNameIdentifier $name): AtomType {
		return $this->complexTypeStorage->addAtom(
			$name,
			$this->complexTypeBuilder->atom($name,
				new AtomValue($this->typeRegistry, $name)
			)
		);
	}

	public function addEnumeration(TypeNameIdentifier $name, array $values): EnumerationType {
		return $this->complexTypeStorage->addEnumerationType(
			$this->complexTypeBuilder->enumeration($name,
				array_map(fn(EnumValueIdentifier $enumValue): EnumerationValue =>
					new EnumerationValue($this->typeRegistry, $name, $enumValue),
					$values
				)
			)
		);
	}

	public function addAlias(TypeNameIdentifier $name, Type $aliasedType): AliasType {
		return $this->complexTypeStorage->addAlias(
			$name,
			$this->complexTypeBuilder->alias($name, $aliasedType)
		);
	}

	public function addData(TypeNameIdentifier $name, Type $valueType): DataType {
		return $this->complexTypeStorage->addData(
			$name,
			$this->complexTypeBuilder->data($name, $valueType)
		);
	}

	public function addOpen(
		TypeNameIdentifier $name,
		Type $valueType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): OpenType {
		$openType = $this->complexTypeStorage->addOpen(
			$name,
			$this->complexTypeBuilder->open($name, $valueType)
		);
		if ($constructorBody !== null) {
			$this->addConstructorMethod($name, $valueType, $errorType, $constructorBody);
		}
		return $openType;
	}

	public function addSealed(
		TypeNameIdentifier $name,
		Type $valueType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): SealedType {
		$sealedType = $this->complexTypeStorage->addSealed(
			$name,
			$this->complexTypeBuilder->sealed($name, $valueType)
		);
		if ($constructorBody !== null) {
			$this->addConstructorMethod($name, $valueType, $errorType, $constructorBody);
		}
		return $sealedType;
	}

	private function addConstructorMethod(
		TypeNameIdentifier $name,
		Type $fromType,
		Type|null $errorType,
		FunctionBody $constructorBody
	): void {
		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->constructor,
			new MethodNameIdentifier('as' . $name->identifier),
			$this->typeRegistry->nameAndType($fromType, null),
			$this->typeRegistry->nameAndType($this->typeRegistry->nothing, null),
			$errorType && !($errorType instanceof NothingType) ?
				$this->typeRegistry->result($fromType, $errorType) :
				$fromType,
			$constructorBody,
		);
	}

}