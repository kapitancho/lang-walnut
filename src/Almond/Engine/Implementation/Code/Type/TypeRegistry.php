<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType as AliasTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType as AnyTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType as FalseTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType as NothingTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType as NullTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType as TrueTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistryCore as TypeRegistryCoreInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\EscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\InvalidNumberRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\ProxyNamedType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\ShapeType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\IntersectionTypeNormalizer;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\UnionTypeNormalizer;

final readonly class TypeRegistry implements TypeRegistryInterface {

	public AnyTypeInterface $any;
	public NothingTypeInterface $nothing;
	public NullTypeInterface $null;
	public BooleanTypeInterface $boolean;
	public FalseTypeInterface $false;
	public TrueTypeInterface $true;

	public TypeRegistryCoreInterface $core;


	private IntersectionTypeNormalizer $intersectionTypeNormalizer;
	private UnionTypeNormalizer $unionTypeNormalizer;

	public function __construct(
		private MethodContext $methodContext,
		public UserlandTypeRegistry $userland,
		private EscapeCharHandler $escapeCharHandler,
	) {
		$this->null = $this->userland->null;
		$this->nothing = new NothingType();
		$this->any = new AnyType();
		$this->boolean = $this->userland->boolean;
		$this->false = $this->userland->false;
		$this->true = $this->userland->true;

		$this->intersectionTypeNormalizer = new IntersectionTypeNormalizer($this);
		$this->unionTypeNormalizer = new UnionTypeNormalizer($this);

		$this->core = new TypeRegistryCore($this->userland);
	}

	public function function(Type $parameterType, Type $returnType): FunctionType {
		return new FunctionType(
			$parameterType,
			$returnType
		);
	}

	public function mutable(Type $valueType): MutableType {
		return new MutableType($valueType);
	}

	public function optionalKey(Type $valueType): OptionalKeyType {
		return new OptionalKeyType($valueType);
	}

	public function shape(Type $refType): ShapeType {
		return new ShapeType($this, $this->methodContext, $refType);
	}

	public function impure(Type $valueType): Type {
		return $this->result($valueType,
			$this->typeByName(
				new TypeName('ExternalError')
			)
		);
	}

	public function result(Type $returnType, Type $errorType): ResultType {
		return new ResultType(
			$returnType,
			$errorType
		);
	}

	public function type(Type $targetType): TypeType {
		return new TypeType(
			$this,
			$targetType
		);
	}

	public function metaType(MetaTypeValue $value): MetaType {
		return new MetaType(
			$value
		);
	}


	public function proxy(TypeName $typeName): AliasTypeInterface {
		return new ProxyNamedType($this, $typeName);
	}


	/** @param list<Type> $types */
	public function union(array $types, bool $normalize = true): Type {
		$types = $this->unionTypeNormalizer->flatten(... $types);
		if (count($types) === 1 && $types[0] instanceof AliasTypeInterface) {
			return $types[0];
		}
		if ($normalize) {
			return $this->unionTypeNormalizer->normalize(... $types);
		}
		return new UnionType($this->unionTypeNormalizer, $types);
	}

	/** @param list<Type> $types */
	public function intersection(array $types): Type {
		$types = $this->intersectionTypeNormalizer->flatten(... $types);
		return $this->intersectionTypeNormalizer->normalize(... $types);
		//return new IntersectionType($this->intersectionTypeNormalizer, $types);
	}

	/** @throws InvalidLengthRange */
	public function string(
		int|Number $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): StringType {
		return new StringType(
			new LengthRange(
				is_int($minLength) ? new Number($minLength) : $minLength,
				is_int($maxLength) ? new Number($maxLength) : $maxLength
			)
		);
	}

	/** @throws InvalidLengthRange */
	public function bytes(
		int|Number $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): BytesType {
		return new BytesType(
			new LengthRange(
				is_int($minLength) ? new Number($minLength) : $minLength,
				is_int($maxLength) ? new Number($maxLength) : $maxLength
			)
		);
	}

	/**
	 * @param list<string> $values
	 * @throws InvalidArgument|DuplicateSubsetValue
	 */
	public function stringSubset(array $values): StringSubsetType {
		return new StringSubsetType(
			$this->string(),
			$this->escapeCharHandler,
			$values);
	}

	public function integerFull(
		NumberInterval ... $intervals
	): IntegerType {
		$numberRange = new NumberRange(true, ...
			count($intervals) === 0 ?
				[new NumberInterval(MinusInfinity::value, PlusInfinity::value)] :
				$intervals
		);
		return new IntegerType($numberRange);
	}

	public function nonZeroInteger(): IntegerType {
		return $this->integerFull(
			new NumberInterval(MinusInfinity::value, new NumberIntervalEndpoint(new Number(0), false)),
			new NumberInterval(new NumberIntervalEndpoint(new Number(0), false), PlusInfinity::value)
		);
	}

	/** @throws InvalidNumberRange */
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
	 * @throws InvalidArgument|DuplicateSubsetValue
	 */
	public function integerSubset(array $values): IntegerSubsetType {
		return new IntegerSubsetType($values);
	}

	public function realFull(
		NumberInterval ... $intervals
	): RealType {
		$numberRange = new NumberRange(true, ...
			count($intervals) === 0 ?
				[new NumberInterval(MinusInfinity::value, PlusInfinity::value)] :
				$intervals
		);
		return new RealType($numberRange);
	}

	public function nonZeroReal(): RealType {
		return $this->realFull(
			new NumberInterval(MinusInfinity::value, new NumberIntervalEndpoint(new Number(0), false)),
			new NumberInterval(new NumberIntervalEndpoint(new Number(0), false), PlusInfinity::value)
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
	 * @throws InvalidArgument|DuplicateSubsetValue
	 */
	public function realSubset(array $values): RealSubsetType {
		return new RealSubsetType($values);
	}

	public function array(
		Type|null               $itemType = null,
		int|Number              $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): ArrayType {
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
		Type|null               $itemType = null,
		int|Number              $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value,
		Type|null               $keyType = null,
	): MapType {
		if ($maxLength === 0 || ($maxLength instanceof Number && (string)$maxLength === '0')) {
			$itemType = $this->nothing;
		}
		$str = $this->string();
		return new MapType(
			$str,
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
		Type|null               $itemType = null,
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

	public function tuple(array $types, Type|null $restType): TupleType {
		return new TupleType(
			$this,
			$types,
			$restType ?? $this->nothing
		);
	}

	public function record(array $types, Type|null $restType): RecordType {
		return new RecordType(
			$this,
			$types,
			$restType ?? $this->nothing
		);
	}

	/** @throws UnknownType */
	public function typeByName(TypeName $typeName): Type {
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
			default => $this->userland->withName($typeName)
		};
	}

}