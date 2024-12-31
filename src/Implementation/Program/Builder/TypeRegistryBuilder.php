<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder as TypeRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\AliasType as AliasTypeInterface;
use Walnut\Lang\Blueprint\Type\AtomType as AtomTypeInterface;
use Walnut\Lang\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\NamedType as NamedTypeInterface;
use Walnut\Lang\Blueprint\Type\RecordType as RecordTypeInterface;
use Walnut\Lang\Blueprint\Type\ResultType as ResultTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Program\Type\IntersectionTypeNormalizer;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Range\IntegerRange;
use Walnut\Lang\Implementation\Range\LengthRange;
use Walnut\Lang\Implementation\Range\RealRange;
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
use Walnut\Lang\Implementation\Type\OptionalKeyType;
use Walnut\Lang\Implementation\Type\ProxyNamedType;
use Walnut\Lang\Implementation\Type\RealSubsetType;
use Walnut\Lang\Implementation\Type\RealType;
use Walnut\Lang\Implementation\Type\RecordType;
use Walnut\Lang\Implementation\Type\ResultType;
use Walnut\Lang\Implementation\Type\SealedType;
use Walnut\Lang\Implementation\Type\StringSubsetType;
use Walnut\Lang\Implementation\Type\StringType;
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
	/** @var array<string, SealedType> */
    private array $sealedTypes;

    private UnionTypeNormalizer $unionTypeNormalizer;
    private IntersectionTypeNormalizer $intersectionTypeNormalizer;

    private const string booleanTypeName = 'Boolean';
    private const string nullTypeName = 'Null';
    private const string constructorTypeName = 'Constructor';

    public function __construct() {
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
		$this->sealedTypes = [];
    }

    public function integer(
	    int|MinusInfinity $min = MinusInfinity::value,
        int|PlusInfinity $max = PlusInfinity::value
    ): IntegerType {
        return new IntegerType(
			$this,
			new IntegerRange($min, $max)
        );
    }
	/** @param list<IntegerValue> $values */
	public function integerSubset(array $values): IntegerSubsetType {
		return new IntegerSubsetType($values);
	}

    public function real(
	    float|MinusInfinity $min = MinusInfinity::value,
	    float|PlusInfinity $max = PlusInfinity::value
    ): RealType {
        return new RealType(new RealRange($min, $max));
    }
	/** @param list<RealValue> $values */
	public function realSubset(array $values): RealSubsetType {
		return new RealSubsetType($values);
	}

    public function string(
	    int $minLength = 0,
	    int|PlusInfinity $maxLength = PlusInfinity::value
    ): StringType {
        return new StringType(new LengthRange($minLength, $maxLength));
    }
	/** @param list<StringValue> $values */
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
			default => $this->withName($typeName)
	    };
	}

    public function withName(TypeNameIdentifier $typeName): NamedTypeInterface {
	    return $this->atomTypes[$typeName->identifier] ??
            $this->enumerationTypes[$typeName->identifier] ??
            $this->aliasTypes[$typeName->identifier] ??
            $this->subtypeTypes[$typeName->identifier] ??
            $this->sealedTypes[$typeName->identifier] ??
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

	public function array(Type|null $itemType = null, int $minLength = 0, int|PlusInfinity $maxLength = PlusInfinity::value): ArrayType {
		return new ArrayType($itemType ?? $this->any, new LengthRange($minLength, $maxLength));
	}

	public function map(Type|null $itemType = null, int $minLength = 0, int|PlusInfinity $maxLength = PlusInfinity::value): MapType {
		return new MapType($itemType ?? $this->any, new LengthRange($minLength, $maxLength));
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

	public function addSubtype(TypeNameIdentifier $name, Type $baseType): SubtypeType {
		$result = new SubtypeType($name, $baseType);
		$this->subtypeTypes[$name->identifier] = $result;
		return $result;
	}

	public function addSealed(
		TypeNameIdentifier $name,
		RecordTypeInterface $valueType
	): SealedType {
		$result = new SealedType($name, $valueType);
		$this->sealedTypes[$name->identifier] = $result;
		return $result;
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
			'sealedTypes' => $this->sealedTypes
		];
	}
}