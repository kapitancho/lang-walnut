<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Native\NativeCodeTypeMapper as NativeCodeTypeMapperInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ShapeType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnionType;

final readonly class NativeCodeTypeMapper implements NativeCodeTypeMapperInterface {
	private const array typeMap = [
		ArrayType::class => ['Array'],
		MapType::class => ['Map'],
		SetType::class => ['Set'],
		TupleType::class => ['Tuple', 'Array'],
		RecordType::class => ['Record', 'Map'],
		IntegerType::class => ['Integer', 'Real'],
		IntegerSubsetType::class => ['Integer', 'Real'],
		RealType::class => ['Real'],
		RealSubsetType::class => ['Real'],
		BytesType::class => ['Bytes'],
		StringType::class => ['String'],
		StringSubsetType::class => ['String'],
		BooleanType::class => ['Boolean', 'Enumeration'],
		TrueType::class => ['True', 'Boolean', 'Enumeration'],
		FalseType::class => ['False', 'Boolean', 'Enumeration'],
		NullType::class => ['Null', 'Atom'],
		EnumerationType::class => ['Enumeration'],
		EnumerationSubsetType::class => ['Enumeration'],
		AtomType::class => ['Atom'],
		DataType::class => ['Data'],
		OpenType::class => ['Open'],
		SealedType::class => ['Sealed'],
		AliasType::class => [/*'Alias'*/],
		FunctionType::class => ['Function'],
		MutableType::class => ['Mutable'],
		ShapeType::class => ['Shape'],
		TypeType::class => ['Type'],
		UnionType::class => [/*'Union'*/],
		IntersectionType::class => [/*'Intersection'*/],
		NothingType::class => ['Nothing'],
		ResultType::class => ['Result'],
		AnyType::class => [],
	];

	/**
	 * @param Type $type
	 * @return array<string>
	 */
	private function findTypesFor(Type $type): array {
		$baseIds = [];
		while ($type instanceof AliasType) {
			$baseIds[] = $type->name->identifier;
			$type = $type->aliasedType;
		}

		foreach (self::typeMap as $typeClass => $ids) {
			if ($type instanceof $typeClass) {
				return array_merge($baseIds, $ids);
			}
		}
		if ($type instanceof MetaType) {
			$class = [
				'Function' => FunctionType::class,
				'MutableValue' => MutableType::class,
				'Tuple' => TupleType::class,
				'Record' => RecordType::class,
				'Union' => UnionType::class,
				'Intersection' => IntersectionType::class,
				'Enumeration' => EnumerationType::class,
				'Data' => DataType::class,
				'Open' => OpenType::class,
			][$type->value->value] ?? null;
			return array_merge($baseIds,
				self::typeMap[$class] ?? []
			);
		}
		// @codeCoverageIgnoreStart
		return []; //should never reach this point
		// @codeCoverageIgnoreEnd
	}

	private function isJsonType(Type $type): bool {
		$typeStr = (string)$type;
		return match(true) {
			$typeStr === '[:]', $typeStr === '[]' => true,
			$type instanceof IntegerType,
				$type instanceof RealType,
				$type instanceof StringType,
				$type instanceof BooleanType, $type instanceof TrueType,
				$type instanceof FalseType, $type instanceof NullType => true,
			$type instanceof ArrayType => $this->isJsonType($type->itemType),
			$type instanceof MapType => $this->isJsonType($type->itemType),
			$type instanceof SetType => $this->isJsonType($type->itemType),
			$type instanceof TupleType => $this->isJsonType($type->asArrayType()),
			$type instanceof RecordType => $this->isJsonType($type->asMapType()),
			$type instanceof MutableType => $this->isJsonType($type->valueType),
			$type instanceof AliasType && $type->name->identifier === 'JsonValue' => true,
			$type instanceof AliasType => $this->isJsonType($type->aliasedType),
			$type instanceof UnionType => array_reduce($type->types,
				fn($carry, $type) => $carry && $this->isJsonType($type), true),
			default => false
		};
	}

	/**
	 * @param Type $type
	 * @return array<TypeName>
	 */
	public function getTypesFor(Type $type): array {
		$result = $this->findTypesFor($type);
		if ($this->isJsonType($type)) {
			$result[] = 'JsonValue';
		}
		$result[] = 'Any';
		if ($type instanceof NamedType && ($result[0] ?? null) !== $type->name->identifier) {
			array_unshift($result, $type->name->identifier);
		}
		return array_map(fn(string $typeName): TypeName => new TypeName($typeName), $result);
	}
}