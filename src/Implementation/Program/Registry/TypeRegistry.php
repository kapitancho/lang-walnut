<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Program\Registry\ComplexTypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\AliasType as AliasTypeInterface;
use Walnut\Lang\Blueprint\Type\AnyType as AnyTypeInterface;
use Walnut\Lang\Blueprint\Type\AtomType as AtomTypeInterface;
use Walnut\Lang\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\FalseType as FalseTypeInterface;
use Walnut\Lang\Blueprint\Type\InvalidMapKeyType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\NothingType as NothingTypeInterface;
use Walnut\Lang\Blueprint\Type\NullType as NullTypeInterface;
use Walnut\Lang\Blueprint\Type\ResultType as ResultTypeInterface;
use Walnut\Lang\Blueprint\Type\TrueType as TrueTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Common\Range\LengthRange;
use Walnut\Lang\Implementation\Program\Type\IntersectionTypeNormalizer;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\AnyType;
use Walnut\Lang\Implementation\Type\ArrayType;
use Walnut\Lang\Implementation\Type\AtomType;
use Walnut\Lang\Implementation\Type\BooleanType;
use Walnut\Lang\Implementation\Type\FalseType;
use Walnut\Lang\Implementation\Type\FunctionType;
use Walnut\Lang\Implementation\Type\IntersectionType;
use Walnut\Lang\Implementation\Type\MapType;
use Walnut\Lang\Implementation\Type\MetaType;
use Walnut\Lang\Implementation\Type\MutableType;
use Walnut\Lang\Implementation\Type\NameAndType;
use Walnut\Lang\Implementation\Type\NothingType;
use Walnut\Lang\Implementation\Type\NullType;
use Walnut\Lang\Implementation\Type\OptionalKeyType;
use Walnut\Lang\Implementation\Type\ProxyNamedType;
use Walnut\Lang\Implementation\Type\RecordType;
use Walnut\Lang\Implementation\Type\ResultType;
use Walnut\Lang\Implementation\Type\SetType;
use Walnut\Lang\Implementation\Type\ShapeType;
use Walnut\Lang\Implementation\Type\TrueType;
use Walnut\Lang\Implementation\Type\TupleType;
use Walnut\Lang\Implementation\Type\TypeType;
use Walnut\Lang\Implementation\Type\UnionType;
use Walnut\Lang\Implementation\Value\AtomValue;
use Walnut\Lang\Implementation\Value\BooleanValue;
use Walnut\Lang\Implementation\Value\NullValue;

final readonly class TypeRegistry implements TypeRegistryInterface {
	private const string booleanTypeName = 'Boolean';
	private const string nullTypeName = 'Null';
	private const string constructorTypeName = 'Constructor';

	use SimpleTypeRegistry;

	public AnyTypeInterface $any;
	public NothingTypeInterface $nothing;
	public NullTypeInterface $null;
	public AtomTypeInterface $constructor;
	public BooleanTypeInterface $boolean;
	public TrueTypeInterface $true;
	public FalseTypeInterface $false;

	private UnionTypeNormalizer $unionTypeNormalizer;
	private IntersectionTypeNormalizer $intersectionTypeNormalizer;

	public TypeRegistryCore $core;

	public function __construct(
		private MethodFinder        $methodFinder,
		private EscapeCharHandler   $escapeCharHandler,
		public ComplexTypeRegistry $complex,
	) {
		$this->unionTypeNormalizer = new UnionTypeNormalizer($this);
		$this->intersectionTypeNormalizer = new IntersectionTypeNormalizer($this);

		$this->any = new AnyType();
		$this->nothing = new NothingType();
		$this->null = new NullType(new TypeNameIdentifier(self::nullTypeName),
			new NullValue($this)
		);
		$this->constructor = new AtomType(
			$c = CoreType::Constructor->typeName(),
			new AtomValue($this->complex, $c)
		);
		$this->boolean = new BooleanType(
			new TypeNameIdentifier(self::booleanTypeName),
			$trueValue = new BooleanValue($this,
				new EnumValueIdentifier('True'), true),
			$falseValue = new BooleanValue($this,
				new EnumValueIdentifier('False'), false)
		);
		$this->true = new TrueType($this->boolean, $trueValue);
		$this->false = new FalseType($this->boolean, $falseValue);

		$this->core = new TypeRegistryCore($this->complex);
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


	public function function(Type $parameterType, Type $returnType): FunctionType {
		return new FunctionType($this, $parameterType, $returnType);
	}

	/** @param list<Type> $itemTypes */
	public function tuple(array $itemTypes, Type|null $restType = null): TupleType {
		return new TupleType($this, $itemTypes, $restType ?? $this->nothing);
	}

	/** @param array<string, Type> $itemTypes */
	public function record(array $itemTypes, Type|null $restType = null): RecordType {
		return new RecordType($this, $itemTypes, $restType ?? $this->nothing);
	}

	public function impure(Type $valueType): Type {
		return $this->result($valueType,
			$this->core->externalError
		);
	}

	/** @throws InvalidLengthRange */
	public function array(Type|null $itemType = null, int|Number $minLength = 0, int|Number|PlusInfinity $maxLength = PlusInfinity::value): ArrayType {
		if ($maxLength === 0 || ($maxLength instanceof Number && (string)$maxLength === '0')) {
			$itemType = $this->nothing;
		}
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
		if ($maxLength === 0 || ($maxLength instanceof Number && (string)$maxLength === '0')) {
			$itemType = $this->nothing;
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
		if ($maxLength === 0 || ($maxLength instanceof Number && (string)$maxLength === '0')) {
			$itemType = $this->nothing;
		}
		return new SetType(
			$itemType ?? $this->any,
			new LengthRange(
				is_int($minLength) ? new Number($minLength) : $minLength,
				is_int($maxLength) ? new Number($maxLength) : $maxLength
			)
		);
	}

	public function optionalKey(Type $valueType): OptionalKeyType {
		return new OptionalKeyType($valueType);
	}

	public function result(Type $returnType, Type $errorType): ResultType {
		if ($returnType instanceof ResultTypeInterface) {
			$errorType = $this->union([$errorType, $returnType->errorType]);
			$returnType = $returnType->returnType;
		}
		return new ResultType($returnType, $errorType);
	}

	public function mutable(Type $valueType): MutableType {
		return new MutableType($valueType);
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
		}
		return new UnionType($this->unionTypeNormalizer, ...$types);
		// @codeCoverageIgnoreEnd
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
			'Bytes' => $this->bytes(),
			'Shape' => $this->shape($this->any),
			'Atom' => $this->metaType(MetaTypeValue::Atom),
			//'Record' => $this->metaType(MetaTypeValue::Record),
			//'Result' => $this->result($this->any, $this->any),
			'Data' => $this->metaType(MetaTypeValue::Data),
			'Open' => $this->metaType(MetaTypeValue::Open),
			'Sealed' => $this->metaType(MetaTypeValue::Sealed),
			'Named' => $this->metaType(MetaTypeValue::Named),
			//'Tuple' => $this->metaType(MetaTypeValue::Tuple),
			//'Alias' => $this->metaType(MetaTypeValue::Alias),
			'Enumeration' => $this->metaType(MetaTypeValue::Enumeration),
			//'Union' => $this->metaType(MetaTypeValue::Union),
			//'Intersection' => $this->metaType(MetaTypeValue::Intersection),
			default => $this->withName($typeName)
		};
	}

	public function nameAndType(Type $type, VariableNameIdentifier|null $name): NameAndType {
		return new NameAndType($type, $name);
	}

	// Proxy the complex type registry methods
	public function withName(TypeNameIdentifier $typeName): NamedType {
		return $this->complex->withName($typeName);
	}

}