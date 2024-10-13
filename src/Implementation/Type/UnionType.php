<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType as UnionTypeInterface;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;

final readonly class UnionType implements UnionTypeInterface, SupertypeChecker, JsonSerializable {
	/** @var non-empty-list<Type> $types */
	private array $types;

	public function __construct(
		private UnionTypeNormalizer $normalizer,
        Type ... $types
	) {
		$this->types = $types;
	}

    /**
     * @return list<Type>
     */
    public function types(): array {
        return $this->types;
    }

    public function isSubtypeOf(Type $ofType): bool {
        foreach($this->types as $type) {
            if (!$type->isSubtypeOf($ofType)) {
                return ($ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this)) ||
	                $this->isRecordUnion($ofType);
            }
        }
        return true;
    }

    public function isSupertypeOf(Type $ofType): bool {
        foreach($this->types as $type) {
            if ($ofType->isSubtypeOf($type)) {
                return true;
            }
        }
        return false;
    }

	public function __toString(): string {
		return sprintf("(%s)", implode('|', $this->types));
	}

	public function jsonSerialize(): array {
		return ['type' => 'Union', 'types' => $this->types];
	}

	private function isRecordUnion(Type $ofType): bool {
		if (!$ofType instanceof RecordType) {
			return false;
		}
		$types = $this->types;
		foreach($types as $type) {
			if (!$type instanceof RecordType) {
				return false;
			}
		}
		/** @var RecordType[] $types */
		$allKeys = array_values(
			array_unique(
				array_merge(...
					array_map(static fn(RecordType $recordType): array =>
						array_keys($recordType->types()), $types))));

		foreach ($allKeys as $key) {
			$propertyTypes = [];
			foreach($types as $type) {
				$propertyTypes[] = $type->types()[$key] ?? $type->restType();
			}
			$propertyType = $this->normalizer->normalize(... $propertyTypes);
			if (!$propertyType->isSubtypeOf($ofType->types()[$key] ?? $ofType->restType())) {
				return false;
			}
		}
		$restTypes = [];
		foreach($types as $type) {
			$restTypes[] = $type->restType();
		}
		$restType = $this->normalizer->normalize(... $restTypes);
		if (!$restType->isSubtypeOf($ofType->restType())) {
			return false;
		}
		return true;
	}
}