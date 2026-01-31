<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ShapeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NativeCodeTypeMapper as NativeCodeTypeMapperInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

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