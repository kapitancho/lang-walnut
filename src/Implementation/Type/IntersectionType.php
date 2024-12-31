<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\IntersectionType as IntersectionTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Program\Type\IntersectionTypeNormalizer;

final readonly class IntersectionType implements IntersectionTypeInterface, SupertypeChecker, JsonSerializable {
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
		if (!$ofType instanceof RecordType) {
			return false;
		}
		$types = $this->types;
		if (array_any($types, fn($type) => !$type instanceof RecordType)) {
			return false;
		}
		/** @var RecordType[] $types */
		$allKeys = array_values(
			array_unique(
				array_merge(...
					array_map(static fn(RecordType $recordType): array =>
						array_keys($recordType->types), $types))));

		foreach ($allKeys as $key) {
			$propertyTypes = [];
			foreach($types as $type) {
				$propertyTypes[] = $type->types[$key] ?? $type->restType;
			}
			$propertyType = $this->normalizer->normalize(... $propertyTypes);
			if (!$propertyType->isSubtypeOf($ofType->types[$key] ?? $ofType->restType)) {
				return false;
			}
		}
		$restTypes = [];
		foreach($types as $type) {
			$restTypes[] = $type->restType;
		}
		$restType = $this->normalizer->normalize(... $restTypes);
		if (!$restType->isSubtypeOf($ofType->restType)) {
			return false;
		}

		return true;
	}
}