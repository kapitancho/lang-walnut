<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\TrueType as TrueTypeInterface;
use Walnut\Lang\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;

final readonly class TrueType implements TrueTypeInterface, JsonSerializable {
	/** @var array<string, BooleanValue> $subsetValues */
	public array $subsetValues;

    public function __construct(
        public BooleanTypeInterface $enumeration,
        public BooleanValue $value
    ) {
		$this->subsetValues = [$this->value->name->identifier => $this->value];
    }

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof TrueTypeInterface, $ofType instanceof BooleanTypeInterface => true,
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		return 'True';
	}

	public function jsonSerialize(): array {
		return ['type' => 'True'];
	}

}