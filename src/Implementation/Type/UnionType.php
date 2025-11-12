<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType as UnionTypeInterface;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\Helper\UnionIntersectionHelper;

final readonly class UnionType implements UnionTypeInterface, SupertypeChecker, JsonSerializable {
	use UnionIntersectionHelper;

	/** @var non-empty-list<Type> $types */
	public array $types;

	public function __construct(
		private UnionTypeNormalizer $normalizer,
        Type ... $types
	) {
		$this->types = $types;
	}

    public function isSubtypeOf(Type $ofType): bool {
	    if (array_any($this->types, fn($type) => !$type->isSubtypeOf($ofType))) {
		    return ($ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this)) || $this->isRecordUnion($ofType);
	    }
        return true;
    }

    public function isSupertypeOf(Type $ofType): bool {
	    return array_any($this->types, fn($type) => $ofType->isSubtypeOf($type));
    }

	public function __toString(): string {
		return sprintf("(%s)", implode('|', $this->types));
	}

	public function jsonSerialize(): array {
		return ['type' => 'Union', 'types' => $this->types];
	}

	private function isRecordUnion(Type $ofType): bool {
		return $this->isRecordHelper($ofType);
	}
}