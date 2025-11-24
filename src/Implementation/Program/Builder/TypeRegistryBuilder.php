<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use BcMath\Number;
use InvalidArgumentException;
use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberInterval as NumberIntervalInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder as TypeRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AliasType as AliasTypeInterface;
use Walnut\Lang\Blueprint\Type\AtomType as AtomTypeInterface;
use Walnut\Lang\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Blueprint\Type\FalseType as FalseTypeInterface;
use Walnut\Lang\Blueprint\Type\InvalidMapKeyType;
use Walnut\Lang\Blueprint\Type\NamedType as NamedTypeInterface;
use Walnut\Lang\Blueprint\Type\ResultType as ResultTypeInterface;
use Walnut\Lang\Blueprint\Type\TrueType as TrueTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Implementation\Common\Range\LengthRange;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Common\Range\NumberRange;
use Walnut\Lang\Implementation\Program\Type\IntersectionTypeNormalizer;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\AliasType;
use Walnut\Lang\Implementation\Type\AnyType;
use Walnut\Lang\Implementation\Type\ArrayType;
use Walnut\Lang\Implementation\Type\AtomType;
use Walnut\Lang\Implementation\Type\BooleanType;
use Walnut\Lang\Implementation\Type\DataType;
use Walnut\Lang\Implementation\Type\EnumerationType;
use Walnut\Lang\Implementation\Type\FunctionType;
use Walnut\Lang\Implementation\Type\IntegerSubsetType;
use Walnut\Lang\Implementation\Type\IntegerType;
use Walnut\Lang\Implementation\Type\IntersectionType;
use Walnut\Lang\Implementation\Type\MapType;
use Walnut\Lang\Implementation\Type\MetaType;
use Walnut\Lang\Implementation\Type\MutableType;
use Walnut\Lang\Implementation\Type\NameAndType;
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
use Walnut\Lang\Implementation\Type\TupleType;
use Walnut\Lang\Implementation\Type\TypeType;
use Walnut\Lang\Implementation\Type\UnionType;
use Walnut\Lang\Implementation\Value\AtomValue;
use Walnut\Lang\Implementation\Value\BooleanValue;
use Walnut\Lang\Implementation\Value\EnumerationValue;
use Walnut\Lang\Implementation\Value\NullValue;

final class TypeRegistryBuilder implements TypeRegistry, TypeRegistryBuilderInterface /*, JsonSerializable*/ {

    public AnyType $any;
    public NothingType $nothing;

    public BooleanTypeInterface $boolean;
    public TrueTypeInterface $true;
    public FalseTypeInterface $false;

    public NullType $null;

	/** @var array<string, AtomTypeInterface> */
    private array $atomTypes;
	/** @var array<string, EnumerationTypeInterface> */
    private array $enumerationTypes;
	/** @var array<string, AliasType> */
    private array $aliasTypes;
	/** @var array<string, DataType> */
    private array $dataTypes;
	/** @var array<string, OpenType> */
    private array $openTypes;
	/** @var array<string, SealedType> */
    private array $sealedTypes;

    private UnionTypeNormalizer $unionTypeNormalizer;
    private IntersectionTypeNormalizer $intersectionTypeNormalizer;

    private const string booleanTypeName = 'Boolean';
    private const string nullTypeName = 'Null';
    private const string constructorTypeName = 'Constructor';

    public function __construct(
		private readonly CustomMethodRegistryBuilderInterface $customMethodRegistryBuilder,
	    private readonly MethodFinder $methodFinder,
	    private readonly EscapeCharHandler $escapeCharHandler,
    ) {
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
		/** @var TrueTypeInterface $trueType */
		$trueType = $this->boolean->subsetType([$trueValue]);
        $this->true = $trueType;

		/** @var FalseTypeInterface $falseType false */
	    $falseType = $this->boolean->subsetType([$falseValue]);
        $this->false = $falseType;

        $this->enumerationTypes = $enumerationTypes;
		$this->aliasTypes = [];
	    $this->dataTypes = [];
		$this->openTypes = [];
		$this->sealedTypes = [];

		$this->aliasTypes['JsonValue'] = new AliasType(
			new TypeNameIdentifier('JsonValue'),
			$this->union([
				$this->null,
				$this->boolean,
				$this->integer(),
				$this->real(),
				$this->string(),
				$this->array($this->proxyType(new TypeNameIdentifier('JsonValue'))),
				$this->map($this->proxyType(new TypeNameIdentifier('JsonValue'))),
				$this->set($this->proxyType(new TypeNameIdentifier('JsonValue'))),
				$this->mutable($this->proxyType(new TypeNameIdentifier('JsonValue'))),
				//$this->shape($this->proxyType(new TypeNameIdentifier('JsonValue')))
			], false)
		);
    }

	public function nonZeroInteger(): IntegerType {
		return $this->integerFull(
			new NumberInterval(MinusInfinity::value, new NumberIntervalEndpoint(new Number(0), false)),
			new NumberInterval(new NumberIntervalEndpoint(new Number(0), false), PlusInfinity::value)
		);
	}

	public function nameAndType(Type $type, VariableNameIdentifier|null $name): NameAndType {
		return new NameAndType($type, $name);
	}

	public function integerFull(
		NumberIntervalInterface ... $intervals
	): IntegerType {
		$numberRange = new NumberRange(true, ...
			count($intervals) === 0 ?
				[new NumberInterval(MinusInfinity::value, PlusInfinity::value)] :
				$intervals
		);
		return new IntegerType(
			$numberRange
		);
	}

    public function integer(
	    int|Number|MinusInfinity $min = MinusInfinity::value,
        int|Number|PlusInfinity $max = PlusInfinity::value
    ): IntegerType {
	    $rangeMin = is_int($min) ? new Number($min) : $min;
	    $rangeMax = is_int($max) ? new Number($max) : $max;
        return new IntegerType(
	        new NumberRange(true,
		        new NumberInterval(
			        $rangeMin === MinusInfinity::value ? MinusInfinity::value :
				        new NumberIntervalEndpoint($rangeMin, true),
			        $rangeMax === PlusInfinity::value ? PlusInfinity::value :
				        new NumberIntervalEndpoint($rangeMax, true)
		        )
	        )
        );
    }

	/**
	 * @param list<Number> $values
	 * @throws DuplicateSubsetValue
	 */
	public function integerSubset(array $values): IntegerSubsetType {
		return new IntegerSubsetType($values);
	}

	public function nonZeroReal(): RealType {
		return $this->realFull(
			new NumberInterval(MinusInfinity::value, new NumberIntervalEndpoint(new Number(0), false)),
			new NumberInterval(new NumberIntervalEndpoint(new Number(0), false), PlusInfinity::value)
		);
	}

	public function realFull(
		NumberIntervalInterface ... $intervals
	): RealType {
		$numberRange = new NumberRange(false, ...
			count($intervals) === 0 ?
				[new NumberInterval(MinusInfinity::value, PlusInfinity::value)] :
				$intervals
		);
		return new RealType(
			$numberRange
		);
	}

	public function real(
	    float|Number|MinusInfinity $min = MinusInfinity::value,
	    float|Number|PlusInfinity $max = PlusInfinity::value
    ): RealType {
		$rangeMin = is_float($min) ? new Number((string)$min) : $min;
		$rangeMax = is_float($max) ? new Number((string)$max) : $max;
        return new RealType(
	        new NumberRange(false,
		        new NumberInterval(
			        $rangeMin === MinusInfinity::value ? MinusInfinity::value :
				        new NumberIntervalEndpoint($rangeMin, true),
			        $rangeMax === PlusInfinity::value ? PlusInfinity::value :
				        new NumberIntervalEndpoint($rangeMax, true)
		        )
	        )
		);
    }
	/**
	 * @param list<Number> $values
	 * @throws DuplicateSubsetValue
	 */
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
	/**
	 * @param list<string> $values
	 * @throws DuplicateSubsetValue
	 */
	public function stringSubset(array $values): StringSubsetType {
		return new StringSubsetType($this, $this->escapeCharHandler, $values);
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
		return new ShapeType($this, $this->methodFinder, $refType);
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
		    'Shape' => $this->shape($this->any),
		    'Atom' => $this->metaType(MetaTypeValue::Atom),
		    'EnumerationValue' => $this->metaType(MetaTypeValue::EnumerationValue),
		    'Record' => $this->metaType(MetaTypeValue::Record),
		    //'Result' => $this->result($this->any, $this->any),
		    'Data' => $this->metaType(MetaTypeValue::Data),
		    'Open' => $this->metaType(MetaTypeValue::Open),
		    'Sealed' => $this->metaType(MetaTypeValue::Sealed),
		    'Tuple' => $this->metaType(MetaTypeValue::Tuple),
		    'Alias' => $this->metaType(MetaTypeValue::Alias),
		    'Enumeration' => $this->metaType(MetaTypeValue::Enumeration),
		    'Union' => $this->metaType(MetaTypeValue::Union),
		    'Intersection' => $this->metaType(MetaTypeValue::Intersection),
			default => $this->withName($typeName)
	    };
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
	public function map(
		Type|null $itemType = null,
		int|Number $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value,
		Type|null $keyType = null,
	): MapType {
		if ($keyType && !$keyType->isSubtypeOf($this->string())) {
			throw new InvalidMapKeyType($keyType);
		}
		return new MapType(
			$keyType ?? $this->string(),
			$itemType ?? $this->any,
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
		if ($normalize) {
			try {
				return $this->unionTypeNormalizer->normalize(... $types);
                // @codeCoverageIgnoreStart
			} catch (UnknownType) {}
			// @codeCoverageIgnoreEnd
		}
		return new UnionType($this->unionTypeNormalizer, ...$types);
	}

	/** @param list<Type> $types */
	public function intersection(array $types): Type {
		$types = $this->intersectionTypeNormalizer->flatten(... $types);
		try {
			return $this->intersectionTypeNormalizer->normalize(... $types);
            // @codeCoverageIgnoreStart
		} catch (UnknownType) {}
		return new IntersectionType($this->intersectionTypeNormalizer, ...$types);
		// @codeCoverageIgnoreEnd
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
			$this->atom(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier('as' . $name->identifier),
			$this->nameAndType($fromType, null),
			$this->nameAndType($this->nothing, null),
			$errorType && !($errorType instanceof NothingType) ?
				$this->result($fromType, $errorType) :
				$fromType,
			$constructorBody,
		);
	}

	/*public function jsonSerialize(): array {
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
			'openTypes' => $this->openTypes,
			'sealedTypes' => $this->sealedTypes
		];
	}*/
}