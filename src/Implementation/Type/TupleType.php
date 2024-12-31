<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\ArrayType as ArrayTypeInterface;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\TupleType as TupleTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownProperty;

final readonly class TupleType implements TupleTypeInterface, JsonSerializable {
	/** @param list<Type> $types */
	public function __construct(
		private TypeRegistry $typeRegistry,
		public array $types,
		public Type $restType
	) {}

	public function asArrayType(): ArrayType {
		$l = count($this->types);
		return $this->typeRegistry->array(
			$this->typeRegistry->union([... array_values($this->types), $this->restType]),
			$l,
			$this->restType instanceof NothingType ? $l : PlusInfinity::value,
		);
	}

	/** @throws UnknownProperty */
	public function typeOf(int $index): Type {
		return $this->types[$index] ??
			throw new UnknownProperty($index, (string)$this);
	}

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof TupleTypeInterface => $this->isSubtypeOfTuple($ofType),
			$ofType instanceof ArrayTypeInterface => $this->isSubtypeOfArray($ofType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	private function isSubtypeOfTuple(TupleTypeInterface $ofType): bool {
		if (!$this->restType->isSubtypeOf($ofType->restType)) {
			return false;
		}
		$ofTypes = $ofType->types;
		$usedIndices = [];
		foreach($this->types as $index => $type) {
			if (!$type->isSubtypeOf($ofTypes[$index] ?? $ofType->restType)) {
				return false;
			}
			$usedIndices[$index] = true;
		}
		return array_all($ofTypes, fn($type, $index) => isset($usedIndices[$index]) || isset($this->types[$index]) && !$this->types[$index]->isSubtypeOf($type));
	}

	private function isSubtypeOfArray(ArrayTypeInterface $ofType): bool {
		$itemType = $ofType->itemType;
		if (!$this->restType->isSubtypeOf($itemType)) {
			return false;
		}
		if (array_any($this->types, fn($type) => !$type->isSubtypeOf($itemType))) {
			return false;
		}
		$cnt = count($this->types);
		if ($cnt < $ofType->range->minLength) {
			return false;
		}
		$max = $ofType->range->maxLength;
		return $max === PlusInfinity::value || ($this->restType instanceof NothingType && $cnt <= $max);
	}

	public function __toString(): string {
		$types = $this->types;
		if ($this->restType instanceof AnyType) {
			$types[] = "...";
		} elseif (!$this->restType instanceof NothingType) {
			$types[] = "... " . $this->restType;
		}
		return "[" . implode(', ', $types) . "]";
	}

	public function jsonSerialize(): array {
		return ['type' => 'Tuple', 'types' => $this->types, 'restType' => $this->restType];
	}

}