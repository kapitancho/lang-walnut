<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder as TypeRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AliasType as AliasTypeInterface;
use Walnut\Lang\Blueprint\Type\AtomType as AtomTypeInterface;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Blueprint\Type\NamedType as NamedTypeInterface;
use Walnut\Lang\Blueprint\Type\RecordType as RecordTypeInterface;
use Walnut\Lang\Blueprint\Type\ResultType as ResultTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Implementation\Common\Range\IntegerRange;
use Walnut\Lang\Implementation\Common\Range\LengthRange;
use Walnut\Lang\Implementation\Common\Range\RealRange;
use Walnut\Lang\Implementation\Program\Type\IntersectionTypeNormalizer;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\AliasType;
use Walnut\Lang\Implementation\Type\AnyType;
use Walnut\Lang\Implementation\Type\ArrayType;
use Walnut\Lang\Implementation\Type\AtomType;
use Walnut\Lang\Implementation\Type\BooleanType;
use Walnut\Lang\Implementation\Type\EnumerationType;
use Walnut\Lang\Implementation\Type\FalseType;
use Walnut\Lang\Implementation\Type\FunctionType;
use Walnut\Lang\Implementation\Type\IntegerSubsetType;
use Walnut\Lang\Implementation\Type\IntegerType;
use Walnut\Lang\Implementation\Type\IntersectionType;
use Walnut\Lang\Implementation\Type\MapType;
use Walnut\Lang\Implementation\Type\MetaType;
use Walnut\Lang\Implementation\Type\MutableType;
use Walnut\Lang\Implementation\Type\NothingType;
use Walnut\Lang\Implementation\Type\NullType;
use Walnut\Lang\Implementation\Type\OpenType;
use Walnut\Lang\Implementation\Type\OptionalKeyType;
use Walnut\Lang\Implementation\Type\ProxyNamedType;
use Walnut\Lang\Implementation\Type\RealSubsetType;
use Walnut\Lang\Implementation\Type\RealType;
use Walnut\Lang\Implementation\Type\RecordType;
use Walnut\Lang\Implementation\Type\ResultType;
use Walnut\Lang\Implementation\Type\SealedType;
use Walnut\Lang\Implementation\Type\SetType;
use Walnut\Lang\Implementation\Type\ShapeType;
use Walnut\Lang\Implementation\Type\StringSubsetType;
use Walnut\Lang\Implementation\Type\StringType;
use Walnut\Lang\Implementation\Type\SubsetType;
use Walnut\Lang\Implementation\Type\SubtypeType;
use Walnut\Lang\Implementation\Type\TrueType;
use Walnut\Lang\Implementation\Type\TupleType;
use Walnut\Lang\Implementation\Type\TypeType;
use Walnut\Lang\Implementation\Type\UnionType;
use Walnut\Lang\Implementation\Value\AtomValue;
use Walnut\Lang\Implementation\Value\BooleanValue;
use Walnut\Lang\Implementation\Value\EnumerationValue;
use Walnut\Lang\Implementation\Value\NullValue;

final class TypeRegistryBuilder implements TypeRegistry, TypeRegistryBuilderInterface, JsonSerializable {

    public AnyType $any;
    public NothingType $nothing;

    public BooleanType $boolean;
    public TrueType $true;
    public FalseType $false;

    public NullType $null;

	/** @var array<string, AtomType> */
    private array $atomTypes;
	/** @var array<string, EnumerationType> */
    private array $enumerationTypes;
	/** @var array<string, AliasType> */
    private array $aliasTypes;
	/** @var array<string, SubtypeType> */
    private array $subtypeTypes;
	/** @var array<string, OpenType> */
    private array $openTypes;
	/** @var array<string, SealedType> */
    private array $sealedTypes;
	/** @var array<string, SubsetType> */
    private array $subsetTypes;

    private UnionTypeNormalizer $unionTypeNormalizer;
    private IntersectionTypeNormalizer $intersectionTypeNormalizer;

    private const string booleanTypeName = 'Boolean';
    private const string nullTypeName = 'Null';
    private const string constructorTypeName = 'Constructor';

    public function __construct(private readonly CustomMethodRegistryBuilderInterface $customMethodRegistryBuilder) {
        $this->unionTypeNormalizer = new UnionTypeNormalizer($this);
        $this->intersectionTypeNormalizer = new IntersectionTypeNormalizer($this);

        $this->any = new AnyType;
        $this->nothing = new NothingType;
        $atomTypes = [
			self::nullTypeName => $this->null = new NullType(
				new TypeNameIdentifier(self::nullTypeName),
	            new NullValue($this)
	        ),
	        self::constructorTypeName => new AtomType(
				$ctn = new TypeNameIdentifier(self::constructorTypeName),
                new AtomValue($this, $ctn)
	        )
        ];

        $this->atomTypes = $atomTypes;
        $enumerationTypes = [
			self::booleanTypeName => $this->boolean = new BooleanType(
	            new TypeNameIdentifier(self::booleanTypeName),
	            new BooleanValue(
					$this,
					$trueValue = new EnumValueIdentifier('True'),
	                true
	            ),
	            new BooleanValue(
					$this,
					$falseValue = new EnumValueIdentifier('False'),
	                false
	            )
	        )
        ];
        $this->true = $this->boolean->subsetType([$trueValue]);
        $this->false = $this->boolean->subsetType([$falseValue]);
        $this->enumerationTypes = $enumerationTypes;
		$this->aliasTypes = [];
		$this->subtypeTypes = [];
		$this->openTypes = [];
		$this->sealedTypes = [];
		$this->subsetTypes = [];
    }

    public function integer(
	    int|Number|MinusInfinity $min = MinusInfinity::value,
        int|Number|PlusInfinity $max = PlusInfinity::value
    ): IntegerType {
        return new IntegerType(
			$this,
			new IntegerRange(
				is_int($min) ? new Number($min) : $min,
				is_int($max) ? new Number($max) : $max
			)
        );
    }
	/** @param list<Number> $values */
	public function integerSubset(array $values): IntegerSubsetType {
		return new IntegerSubsetType($values);
	}

    public function real(
	    float|Number|MinusInfinity $min = MinusInfinity::value,
	    float|Number|PlusInfinity $max = PlusInfinity::value
    ): RealType {
        return new RealType(
			new RealRange(
				is_float($min) ? new Number((string)$min) : $min,
				is_float($max) ? new Number((string)$max) : $max
			)
        );
    }
	/** @param list<Number> $values */
	public function realSubset(array $values): RealSubsetType {
		return new RealSubsetType($values);
	}

    public function string(
	    int|Number $minLength = 0,
	    int|Number|PlusInfinity $maxLength = PlusInfinity::value
    ): StringType {
        return new StringType(new LengthRange(
	        is_int($minLength) ? new Number($minLength) : $minLength,
            is_int($maxLength) ? new Number($maxLength) : $maxLength
        ));
    }
	/** @param list<string> $values */
	public function stringSubset(array $values): StringSubsetType {
		return new StringSubsetType($values);
	}

    public function optionalKey(Type $valueType): OptionalKeyType {
		return new OptionalKeyType($valueType);
	}

    public function impure(Type $valueType): Type {
		return $this->result($valueType,
			$this->withName(new TypeNameIdentifier('ExternalError'))
		);
    }

    public function result(Type $returnType, Type $errorType): ResultType {
		if ($returnType instanceof ResultTypeInterface) {
			$errorType = $this->union([$errorType, $returnType->errorType]);
			$returnType = $returnType->returnType;
		}
        return new ResultType($returnType, $errorType);
    }

	public function shape(Type $refType): ShapeType {
		return new ShapeType($refType);
	}

	public function type(Type $refType): TypeType {
        return new TypeType($refType);
    }

	public function proxyType(TypeNameIdentifier $typeName): ProxyNamedType {
		return new ProxyNamedType($typeName, $this);
	}

	public function metaType(MetaTypeValue $value): MetaType {
		return new MetaType($value);
	}

    public function typeByName(TypeNameIdentifier $typeName): Type {
	    return match($typeName->identifier) {
			'Any' => $this->any,
			'Nothing' => $this->nothing,
			'Array' => $this->array(),
			'Map' => $this->map(),
		    'Error' => $this->result($this->nothing, $this->any),
			'Impure' => $this->impure($this->any),
			'Mutable' => $this->mutable($this->any),
			'Type' => $this->type($this->any),
			'Null' => $this->null,
			'True' => $this->true,
			'False' => $this->false,
			'Boolean' => $this->boolean,
			'Integer' => $this->integer(),
			'Real' => $this->real(),
			'String' => $this->string(),
		    'Atom' => $this->metaType(MetaTypeValue::Atom),
		    'EnumerationValue' => $this->metaType(MetaTypeValue::EnumerationValue),
		    'Record' => $this->metaType(MetaTypeValue::Record),
		    'Open' => $this->metaType(MetaTypeValue::Open),
		    'Sealed' => $this->metaType(MetaTypeValue::Sealed),
		    'Subset' => $this->metaType(MetaTypeValue::Subset),
		    'Subtype' => $this->metaType(MetaTypeValue::Subtype),
		    'Tuple' => $this->metaType(MetaTypeValue::Tuple),
			default => $this->withName($typeName)
	    };
	}

    public function withName(TypeNameIdentifier $typeName): NamedTypeInterface {
	    return $this->atomTypes[$typeName->identifier] ??
            $this->enumerationTypes[$typeName->identifier] ??
            $this->aliasTypes[$typeName->identifier] ??
            $this->subtypeTypes[$typeName->identifier] ??
            $this->openTypes[$typeName->identifier] ??
            $this->sealedTypes[$typeName->identifier] ??
            $this->subsetTypes[$typeName->identifier] ??
            UnknownType::withName($typeName);
    }

	/** @throws UnknownType */
	public function alias(TypeNameIdentifier $typeName): AliasType {
		return $this->aliasTypes[$typeName->identifier] ?? UnknownType::withName($typeName);
	}

	/** @throws UnknownType */
	public function subtype(TypeNameIdentifier $typeName): SubtypeType {
		return $this->subtypeTypes[$typeName->identifier] ?? UnknownType::withName($typeName);
	}

	public function open(TypeNameIdentifier $typeName): OpenType {
		return $this->openTypes[$typeName->identifier] ?? UnknownType::withName($typeName);
	}

	/** @throws UnknownType */
	public function sealed(TypeNameIdentifier $typeName): SealedType {
		return $this->sealedTypes[$typeName->identifier] ?? UnknownType::withName($typeName);
	}

    public function atom(TypeNameIdentifier $typeName): AtomTypeInterface {
        return $this->atomTypes[$typeName->identifier] ?? UnknownType::withName($typeName);
    }

    public function enumeration(TypeNameIdentifier $typeName): EnumerationTypeInterface {
        return $this->enumerationTypes[$typeName->identifier] ?? UnknownType::withName($typeName);
    }

	/**
	  * @param non-empty-list<EnumValueIdentifier> $values
	  * @throws UnknownEnumerationValue|InvalidArgumentException
	  **/
	 public function enumerationSubsetType(TypeNameIdentifier $typeName, array $values): EnumerationSubsetType {
		 return $this->enumeration($typeName)->subsetType($values);
	 }

	/** @throws InvalidLengthRange */
	public function array(Type|null $itemType = null, int|Number $minLength = 0, int|Number|PlusInfinity $maxLength = PlusInfinity::value): ArrayType {
		return new ArrayType(
			$itemType ?? $this->any,
			new LengthRange(
				is_int($minLength) ? new Number($minLength) : $minLength,
		       is_int($maxLength) ? new Number($maxLength) : $maxLength
			)
		);
	}

	/** @throws InvalidLengthRange */
	public function map(Type|null $itemType = null, int|Number $minLength = 0, int|Number|PlusInfinity $maxLength = PlusInfinity::value): MapType {
		return new MapType($itemType ?? $this->any,
			new LengthRange(
				is_int($minLength) ? new Number($minLength) : $minLength,
		       is_int($maxLength) ? new Number($maxLength) : $maxLength
			)
		);
	}

	/** @throws InvalidLengthRange */
	public function set(
		Type|null        $itemType = null,
		int|Number              $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): SetType {
		return new SetType(
			$itemType ?? $this->any,
			new LengthRange(
				is_int($minLength) ? new Number($minLength) : $minLength,
		       is_int($maxLength) ? new Number($maxLength) : $maxLength
			)
		);
	}

	/** @param list<Type> $itemTypes */
	public function tuple(array $itemTypes, Type|null $restType = null): TupleType {
		return new TupleType($this, $itemTypes, $restType ?? $this->nothing);
	}

	/** @param array<string, Type> $itemTypes */
	public function record(array $itemTypes, Type|null $restType = null): RecordType {
		return new RecordType($this, $itemTypes, $restType ?? $this->nothing);
	}

	/** @param list<Type> $types */
	public function union(array $types, bool $normalize = true): Type {
		$types = $this->unionTypeNormalizer->flatten(... $types);
		if (count($types) === 1 && $types[0] instanceof AliasTypeInterface) {
			return $types[0];
		}
		return $normalize ? $this->unionTypeNormalizer->normalize(... $types) :
			new UnionType($this->unionTypeNormalizer, ...$types);
	}

	/** @param list<Type> $types */
	public function intersection(array $types, bool $normalize = true): Type {
		$types = $this->intersectionTypeNormalizer->flatten(... $types);
        return $normalize ? $this->intersectionTypeNormalizer->normalize(... $types) :
            new IntersectionType($this->intersectionTypeNormalizer, ...$types);
	}

	public function function(Type $parameterType, Type $returnType): FunctionType {
		return new FunctionType($parameterType, $returnType);
	}

	public function mutable(Type $valueType): MutableType {
		return new MutableType($valueType);
	}

	public function addAtom(TypeNameIdentifier $name): AtomType {
		$result = new AtomType(
			$name,
			new AtomValue($this, $name)
		);
		$this->atomTypes[$name->identifier] = $result;
		return $result;
	}

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): EnumerationType {
		$result = new EnumerationType(
			$name,
			array_combine(
				array_map(
					static fn(EnumValueIdentifier $value): string => $value->identifier,
					$values
				),
				array_map(
					fn(EnumValueIdentifier $value): EnumerationValue =>
						new EnumerationValue($this, $name, $value),
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

	public function addSubtype(
		TypeNameIdentifier $name,
		Type $baseType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): SubtypeType {
		$result = new SubtypeType($name, $baseType);
		$this->subtypeTypes[$name->identifier] = $result;
		if ($constructorBody) {
			$this->addConstructorMethod($name, $baseType, $errorType, $constructorBody);
		}
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

	public function addSubset(
		TypeNameIdentifier $name,
		Type $valueType,
		FunctionBody|null $constructorBody = null,
		Type|null $errorType = null
	): SubsetType {
		$result = new SubsetType($name, $valueType);
		$this->subsetTypes[$name->identifier] = $result;
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
			$this->atom(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier('as' . $name->identifier),
			$fromType,
			null,
			$this->nothing,
			$errorType && !($errorType instanceof NothingType) ?
				$this->result($fromType, $errorType) :
				$fromType,
			$constructorBody,
		);
	}

	public function jsonSerialize(): array {
		return [
			'coreTypes' => [
				'Any' => $this->any,
				'Nothing' => $this->nothing,
				'Boolean' => $this->boolean,
				'True' => $this->true,
				'False' => $this->false,
				'Null' => $this->null,
				'Integer' => $this->integer(),
				'Real' => $this->real(),
				'String' => $this->string(),
				'Array' => $this->array(),
				'Map' => $this->map(),
				'Tuple' => $this->tuple([], $this->nothing),
				'Record' => $this->record([], $this->nothing),
				'Mutable' => $this->mutable($this->any),
				'Result' => $this->result($this->any, $this->any),
				'Type' => $this->type($this->any),
				'Function' => $this->function($this->any, $this->any),
				'Intersection' => $this->intersection([$this->integer(), $this->string()]),
				'Union' => $this->union([$this->integer(), $this->string()]),
			],
			'atomTypes' => $this->atomTypes,
			'enumerationTypes' => $this->enumerationTypes,
			'aliasTypes' => $this->aliasTypes,
			'subtypeTypes' => $this->subtypeTypes,
			'openTypes' => $this->openTypes,
			'sealedTypes' => $this->sealedTypes
		];
	}
}