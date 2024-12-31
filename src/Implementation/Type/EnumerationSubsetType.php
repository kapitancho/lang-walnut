<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType as EnumerationSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\EnumerationValue;

final readonly class EnumerationSubsetType implements EnumerationSubsetTypeInterface, JsonSerializable {

    /**
     * @param array<string, EnumerationValue> $subsetValues
     */
    public function __construct(
        public EnumerationTypeInterface $enumeration,
        public array $subsetValues
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof EnumerationTypeInterface =>
                $this->enumeration->name->identifier === $ofType->name->identifier,
            $ofType instanceof EnumerationSubsetTypeInterface =>
            	$this->enumeration->name->equals($ofType->enumeration->name) &&
                self::isSubset($this->subsetValues, $ofType->subsetValues),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

    private static function isSubset(array $subset, array $superset): bool {
		foreach($subset as $key => $value) {
			if (!isset($superset[$key])) {
				return false;
			}
		}
		return true;
    }

	public function __toString(): string {
		return sprintf("%s[%s]",
			$this->enumeration,
			implode(', ', array_map(
				static fn(EnumerationValue $value) => $value->name,
				$this->subsetValues
			))
		);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'EnumerationSubsetType',
			'enumerationName' => $this->enumeration->name,
			'subsetValues' => $this->subsetValues
		];
	}
}