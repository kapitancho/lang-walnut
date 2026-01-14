<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ComplexTypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AtomType as AtomTypeInterface;
use Walnut\Lang\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Blueprint\Type\FalseType as FalseTypeInterface;
use Walnut\Lang\Blueprint\Type\NamedType as NamedTypeInterface;
use Walnut\Lang\Blueprint\Type\TrueType as TrueTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Implementation\Type\AliasType;
use Walnut\Lang\Implementation\Type\AtomType;
use Walnut\Lang\Implementation\Type\BooleanType;
use Walnut\Lang\Implementation\Type\DataType;
use Walnut\Lang\Implementation\Type\EnumerationType;
use Walnut\Lang\Implementation\Type\NothingType;
use Walnut\Lang\Implementation\Type\NullType;
use Walnut\Lang\Implementation\Type\OpenType;
use Walnut\Lang\Implementation\Type\SealedType;
use Walnut\Lang\Implementation\Value\AtomValue;
use Walnut\Lang\Implementation\Value\BooleanValue;
use Walnut\Lang\Implementation\Value\EnumerationValue;
use Walnut\Lang\Implementation\Value\NullValue;

final class ComplexTypeRegistryBuilder implements TypeRegistryBuilder, ComplexTypeRegistry {
	private const string booleanTypeName = 'Boolean';
	private const string nullTypeName = 'Null';
	private const string constructorTypeName = 'Constructor';

	public NullType $null;
	public BooleanTypeInterface $boolean;
	public TrueTypeInterface $true;
	public FalseTypeInterface $false;

	/** @var array<string, AtomTypeInterface> */
	private array $atomTypes = [];
	/** @var array<string, EnumerationTypeInterface> */
	private array $enumerationTypes = [];
	/** @var array<string, AliasType> */
	private array $aliasTypes = [];
	/** @var array<string, DataType> */
	private array $dataTypes = [];
	/** @var array<string, OpenType> */
	private array $openTypes = [];
	/** @var array<string, SealedType> */
	private array $sealedTypes = [];

	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly CustomMethodRegistryBuilder $customMethodRegistryBuilder
	) {
		$this->atomTypes[self::nullTypeName] = $this->null = new NullType(
			new TypeNameIdentifier(self::nullTypeName),
			new NullValue($this->typeRegistry)
		);
		$this->atomTypes[self::constructorTypeName] = new AtomType(
			$ctn = new TypeNameIdentifier(self::constructorTypeName),
			new AtomValue($this->typeRegistry, $ctn)
		);

		$this->enumerationTypes[self::booleanTypeName] = $this->boolean = new BooleanType(
			new TypeNameIdentifier(self::booleanTypeName),
			new BooleanValue(
				$this->typeRegistry,
				$trueValue = new EnumValueIdentifier('True'),
				true
			),
			new BooleanValue(
				$this->typeRegistry,
				$falseValue = new EnumValueIdentifier('False'),
				false
			)
		);

		/** @var TrueTypeInterface $trueType */
		$trueType = $this->boolean->subsetType([$trueValue]);
		$this->true = $trueType;

		/** @var FalseTypeInterface $falseType false */
		$falseType = $this->boolean->subsetType([$falseValue]);
		$this->false = $falseType;


		$j = CoreType::JsonValue->typeName();
		$this->aliasTypes['JsonValue'] = new AliasType(
			$j,
			$this->typeRegistry->union([
				$this->null,
				$this->boolean,
				$this->typeRegistry->integer(),
				$this->typeRegistry->real(),
				$this->typeRegistry->string(),
				$this->typeRegistry->array($this->typeRegistry->proxyType($j)),
				$this->typeRegistry->map($this->typeRegistry->proxyType($j)),
				$this->typeRegistry->set($this->typeRegistry->proxyType($j)),
				$this->typeRegistry->mutable($this->typeRegistry->proxyType($j)),
				//$this->shape($this->proxyType($j))
			], false)
		);
	}

	public function addAtom(TypeNameIdentifier $name): AtomType {
		$result = new AtomType(
			$name,
			new AtomValue($this->typeRegistry, $name)
		);
		$this->atomTypes[$name->identifier] = $result;
		return $result;
	}

	/**
	 * @param list<EnumValueIdentifier> $values
	 * @throws DuplicateSubsetValue
	 **/
	public function addEnumeration(TypeNameIdentifier $name, array $values): EnumerationType {
		$keys = [];
		foreach ($values as $value) {
			/** @phpstan-ignore-next-line identical.alwaysTrue */
			if (!$value instanceof EnumValueIdentifier) {
				// @codeCoverageIgnoreStart
				throw new InvalidArgumentException(
					'Expected EnumValueIdentifier, got ' . get_debug_type($value)
				);
				// @codeCoverageIgnoreEnd
			}
			if (in_array($value->identifier, $keys, true)) {
				DuplicateSubsetValue::ofEnumeration(
					$name->identifier,
					$value
				);
			}
			$keys[] = $value->identifier;
		}
		$result = new EnumerationType(
			$name,
			array_combine(
				$keys,
				array_map(
					fn(EnumValueIdentifier $value): EnumerationValue =>
					new EnumerationValue($this->typeRegistry, $name, $value),
					$values
				)
			)
		);
		$this->enumerationTypes[$name->identifier] = $result;
		return $result;
	}

	public function addAlias(TypeNameIdentifier $name, Type $aliasedType): AliasType {
		$result = new AliasType($name, $aliasedType);
		$this->aliasTypes[$name->identifier] = $result;
		return $result;
	}

	public function addData(
		TypeNameIdentifier $name,
		Type $valueType
	): DataType {
		$result = new DataType($name, $valueType);
		$this->dataTypes[$name->identifier] = $result;
		return $result;
	}


	public function addOpen(
		TypeNameIdentifier  $name,
		Type $valueType,
		FunctionBody|null   $constructorBody = null,
		Type|null           $errorType = null
	): OpenType {
		$result = new OpenType($name, $valueType);
		$this->openTypes[$name->identifier] = $result;
		if ($constructorBody) {
			$this->addConstructorMethod($name, $valueType, $errorType, $constructorBody);
		}
		return $result;
	}

	public function addSealed(
		TypeNameIdentifier  $name,
		Type $valueType,
		FunctionBody|null   $constructorBody = null,
		Type|null           $errorType = null
	): SealedType {
		$result = new SealedType($name, $valueType);
		$this->sealedTypes[$name->identifier] = $result;
		if ($constructorBody) {
			$this->addConstructorMethod($name, $valueType, $errorType, $constructorBody);
		}
		return $result;
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


	/** @throws UnknownType */
	public function alias(TypeNameIdentifier $typeName): AliasType {
		return $this->aliasTypes[$typeName->identifier] ?? UnknownType::withName($typeName);
	}

	/** @throws UnknownType */
	public function data(TypeNameIdentifier $typeName): DataType {
		return $this->dataTypes[$typeName->identifier] ?? UnknownType::withName($typeName, 'data');
	}

	/** @throws UnknownType */
	public function open(TypeNameIdentifier $typeName): OpenType {
		return $this->openTypes[$typeName->identifier] ?? UnknownType::withName($typeName, 'open');
	}

	/** @throws UnknownType */
	public function sealed(TypeNameIdentifier $typeName): SealedType {
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