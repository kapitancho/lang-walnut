<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Type\TrueType as TrueTypeInterface;
use Walnut\Lang\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;

final readonly class TrueType implements TrueTypeInterface, JsonSerializable {
	/** @var list<BooleanValue> $subsetValues */
	private array $subsetValues;

    public function __construct(
        private BooleanTypeInterface $enumeration,
        private BooleanValue $trueValue
    ) {
		$this->subsetValues = [$this->trueValue->name()->identifier => $this->trueValue];
    }

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof TrueTypeInterface, $ofType instanceof BooleanTypeInterface => true,
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
		return $this->trueValue;
	}

	public function __toString(): string {
		return 'True';
	}

	public function jsonSerialize(): array {
		return ['type' => 'True'];
	}

}