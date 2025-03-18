<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper as NativeCodeTypeMapperInterface;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;

final readonly class NativeCodeTypeMapper implements NativeCodeTypeMapperInterface {
	private function getTypeMapping(): array {
		return [
			ArrayType::class => ['Array'],
			MapType::class => ['Map'],
			SetType::class => ['Set'],
			TupleType::class => ['Tuple', 'Array'],
			RecordType::class => ['Record', 'Map'],
			IntegerType::class => ['Integer', 'Real'],
			IntegerSubsetType::class => ['Integer', 'Real'],
			RealType::class => ['Real'],
			RealSubsetType::class => ['Real'],
			StringType::class => ['String'],
			StringSubsetType::class => ['String'],
			BooleanType::class => ['Boolean', 'Enumeration'],
			TrueType::class => ['Boolean', 'Enumeration'],
			FalseType::class => ['Boolean', 'Enumeration'],
			NullType::class => ['Null', 'Atom'],
			EnumerationType::class => ['Enumeration'],
			EnumerationSubsetType::class => ['Enumeration'],
			AtomType::class => ['Atom'],
			SubsetType::class => ['Subset'],
			OpenType::class => ['Open'],
			SealedType::class => ['Sealed'],
			AliasType::class => ['Alias'],
			FunctionType::class => ['Function'],
			ResultType::class => ['Result'],
			MutableType::class => ['Mutable'],
			ShapeType::class => ['Shape'],
			TypeType::class => ['Type'],
			UnionType::class => ['Union'],
			IntersectionType::class => ['Intersection'],
			NothingType::class => ['Nothing'],
			AnyType::class => [],
		];
	}

	/**
	 * @param Type $type
	 * @return array<string>
	 */
	private function findTypesFor(Type $type): array {
		$baseIds = [];
		$k = 0;
		$alias = null;
		$subset = null;
		while ($type instanceof AliasType || $type instanceof SubsetType) {
			$k++;
			$baseIds[] = $type->name->identifier;
			if ($type instanceof AliasType) {
				$alias ??= $k;
				$type = $type->aliasedType;
				continue;
			}
			if ($type instanceof SubsetType) {
				$subset ??= $k;
				$type = $type->valueType;
			}
		}
		if ($alias !== null) {
			if ($subset !== null && $subset < $alias) {
				$baseIds[] = 'Subset';
			}
			$baseIds[] = 'Alias';
			if ($subset !== null && $subset > $alias) {
				$baseIds[] = 'Subset';
			}
		} elseif ($subset !== null) {
			$baseIds[] = 'Subset';
		}

		foreach ($this->getTypeMapping() as $typeClass => $ids) {
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
				'Subset' => SubsetType::class,
				'EnumerationValue' => EnumerationType::class,
			][$type->value->value] ?? null;
			return array_merge($baseIds,
				$this->getTypeMapping()[$class] ?? []
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
			$type instanceof IntegerType, $type instanceof IntegerSubsetType,
			$type instanceof RealType, $type instanceof RealSubsetType,
			$type instanceof StringType, $type instanceof StringSubsetType,
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
	 * @return array<string>
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
		return $result;
	}
}