<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\FalseType as FalseTypeInterface;
use Walnut\Lang\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;

final readonly class FalseType implements FalseTypeInterface, JsonSerializable {
	/** @var list<BooleanValue> $subsetValues */
	private array $subsetValues;

    public function __construct(
        private BooleanTypeInterface $enumeration,
        private BooleanValue $falseValue
    ) {
		$this->subsetValues = [$this->falseValue->name()->identifier => $this->falseValue];
    }

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof FalseTypeInterface, $ofType instanceof BooleanTypeInterface => true,
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

    public function enumeration(): BooleanType {
        return $this->enumeration;
    }

    /** @return array<string, EnumerationValue> */
    public function subsetValues(): array {
        return $this->subsetValues;
    }

	public function value(): BooleanValue {
		return $this->falseValue;
	}

	public function __toString(): string {
		return 'False';
	}

	public function jsonSerialize(): array {
		return ['type' => 'False'];
	}
}