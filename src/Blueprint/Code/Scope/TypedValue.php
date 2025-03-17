<?php

namespace Walnut\Lang\Blueprint\Code\Scope;

use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final class TypedValue {

	/** @param list<NamedType|ShapeType> $namedTypes */
	/** @param list<Type> $otherTypes */
	private function __construct(
		public readonly array $namedTypes,
		public readonly array $otherTypes,
		public readonly Value $value,
	) {}

	/** @return array<Type> */
	public array $types {
		get => $this->namedTypes + $this->otherTypes;
	}

	public Type $type {
		get => $this->types[array_key_first($this->types)];
	}

	private function canBeOptimized(Type $type): bool {
		if($type instanceof NamedType || $type instanceof ShapeType) {
			return false;
		}
		if($type instanceof RecordType || $type instanceof TupleType) {
			foreach($type->types as $rType) {
				if (!$this->canBeOptimized($rType)) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * @param array<Type> $types
	 * @return array<Type>
	 */
	private function optimizeTypes(Type $newType, array $types, bool $isAdditional): array {
		foreach($types as $type) {
			if ($this->canBeOptimized($newType)) {
				if ($type->isSubtypeOf($newType)) {
					return $types;
				}
			}
			if ($this->canBeOptimized($type)) {
				if ($newType->isSubtypeOf($type)) {
					unset($types[(string)$type]);
				}
			}
		}
		return ($isAdditional ? [] : [(string)$newType => $newType]) + $types +
			($isAdditional ? [(string)$newType => $newType] : []);
	}

	public function withType(Type $type): self {
		if (array_key_exists((string)$type, $this->types)) {
			return $this;
		}
		if ($type instanceof NamedType || $type instanceof ShapeType) {
			return new self(
				[(string)$type => $type] + $this->namedTypes,
				$this->otherTypes,
				$this->value
			);
		} else {
			return new self(
				$this->namedTypes,
				$this->optimizeTypes($type, $this->otherTypes, false),
				$this->value
			);
		}
	}

	public function isSubtypeOf(Type $type): bool {
		return array_any(
			$this->types,
			fn(Type $t): bool => $t->isSubtypeOf($type)
		);
	}

	public static function forValue(Value $value): self {
		$t = $value->type;
		$arr = [(string)$t => $t];
		return $t instanceof NamedType || $t instanceof ShapeType ?
			new self($arr, [], $value) :
			new self([], $arr, $value);
	}
}