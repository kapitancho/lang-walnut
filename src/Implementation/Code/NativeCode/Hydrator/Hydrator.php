<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Program\Registry\MethodContext;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\HydrationException;

final readonly class Hydrator {

	private SimpleTypeHydrator $simpleTypeHydrator;
	private CompositeTypeHydrator $compositeTypeHydrator;
	private CustomTypeHydrator $customTypeHydrator;
	private TypeTypeHydrator $typeTypeHydrator;

	public function __construct(
		TypeRegistry $typeRegistry,
		ValueRegistry $valueRegistry,
		MethodContext $methodContext,
	) {
		$this->simpleTypeHydrator = new SimpleTypeHydrator($valueRegistry);
		$this->compositeTypeHydrator = new CompositeTypeHydrator($this, $valueRegistry);
		$this->customTypeHydrator = new CustomTypeHydrator(
			$this,
			$typeRegistry,
			$valueRegistry,
			$methodContext,
		);
		$this->typeTypeHydrator = new TypeTypeHydrator($typeRegistry, $valueRegistry);
	}

	/** @throws HydrationException */
	public function hydrate(Value $value, Type $targetType, string $hydrationPath): Value {
		/** @phpstan-ignore-next-line var.type */
		$fn = match(true) {
			$targetType instanceof BooleanType => $this->simpleTypeHydrator->hydrateBoolean(...),
			$targetType instanceof FalseType => $this->simpleTypeHydrator->hydrateFalse(...),
			$targetType instanceof NullType => $this->simpleTypeHydrator->hydrateNull(...),
			$targetType instanceof TrueType => $this->simpleTypeHydrator->hydrateTrue(...),

			$targetType instanceof AnyType => $this->simpleTypeHydrator->hydrateAny(...),
			$targetType instanceof NothingType => $this->simpleTypeHydrator->hydrateNothing(...),
			$targetType instanceof FunctionType => $this->simpleTypeHydrator->hydrateFunction(...),
			$targetType instanceof ArrayType => $this->compositeTypeHydrator->hydrateArray(...),
			$targetType instanceof SetType => $this->compositeTypeHydrator->hydrateSet(...),
			$targetType instanceof AtomType => $this->customTypeHydrator->hydrateAtom(...),
			$targetType instanceof EnumerationType => $this->customTypeHydrator->hydrateEnumeration(...),
			$targetType instanceof EnumerationSubsetType => $this->customTypeHydrator->hydrateEnumerationSubset(...),
			$targetType instanceof IntegerType => $this->simpleTypeHydrator->hydrateInteger(...),
			$targetType instanceof IntersectionType => $this->compositeTypeHydrator->hydrateIntersection(...),
			$targetType instanceof MapType => $this->compositeTypeHydrator->hydrateMap(...),
			$targetType instanceof MutableType => $this->compositeTypeHydrator->hydrateMutable(...),
			$targetType instanceof RealType => $this->simpleTypeHydrator->hydrateReal(...),
			$targetType instanceof RecordType => $this->compositeTypeHydrator->hydrateRecord(...),
			$targetType instanceof StringSubsetType => $this->simpleTypeHydrator->hydrateStringSubset(...),
			$targetType instanceof StringType => $this->simpleTypeHydrator->hydrateString(...),
			$targetType instanceof BytesType => $this->simpleTypeHydrator->hydrateBytes(...),
			$targetType instanceof TupleType => $this->compositeTypeHydrator->hydrateTuple(...),
			$targetType instanceof TypeType => $this->typeTypeHydrator->hydrateType(...),
			$targetType instanceof UnionType => $this->compositeTypeHydrator->hydrateUnion(...),
			$targetType instanceof AliasType => $this->compositeTypeHydrator->hydrateAlias(...),
			$targetType instanceof ResultType => $this->compositeTypeHydrator->hydrateResult(...),
			$targetType instanceof DataType => $this->customTypeHydrator->hydrateData(...),
			$targetType instanceof OpenType => $this->customTypeHydrator->hydrateOpen(...),
			$targetType instanceof SealedType => $this->customTypeHydrator->hydrateSealed(...),
			$targetType instanceof ProxyNamedType => $this->compositeTypeHydrator->hydrateProxyNamed(...),
			// @codeCoverageIgnoreStart
			default => throw new HydrationException(
				$value,
				$hydrationPath,
				"Unsupported type: " . $targetType::class
			)
			// @codeCoverageIgnoreEnd
		};
		/** @phpstan-ignore-next-line argument.type */
		return $fn($value, $targetType, $hydrationPath);
	}

}