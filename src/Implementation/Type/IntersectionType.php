<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\IntersectionType as IntersectionTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Program\Type\IntersectionTypeNormalizer;
use Walnut\Lang\Implementation\Type\Helper\UnionIntersectionHelper;

final readonly class IntersectionType implements IntersectionTypeInterface, SupertypeChecker, JsonSerializable {
	use UnionIntersectionHelper;

	/** @var non-empty-list<Type> $types */
	public array $types;

	public function __construct(
		private IntersectionTypeNormalizer $normalizer,
        Type ... $types
	) {
		$this->types = $types;
	}

    public function isSubtypeOf(Type $ofType): bool {
	    if (array_any($this->types, fn($type) => $type->isSubtypeOf($ofType))) {
		    return true;
	    }
		if ($this->isRecordIntersection($ofType)) {
			return true;
		}
        return $ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this);
    }

    public function isSupertypeOf(Type $ofType): bool {
	    return array_all($this->types, fn($type) => $ofType->isSubtypeOf($type));
    }

	public function __toString(): string {
		return sprintf("(%s)", implode('&', $this->types));
	}

	public function jsonSerialize(): array {
		return ['type' => 'Intersection', 'types' => $this->types];
	}

	private function isRecordIntersection(Type $ofType): bool {
		return $this->isRecordHelper($ofType);
	}
}