<?php

namespace Walnut\Lang\Implementation\Type;

use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType as EnumerationSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\EnumerationValue;

final readonly class EnumerationType implements EnumerationTypeInterface, JsonSerializable {

    /**
     * @param array<string, EnumerationValue> $enumerationValues
     */
    public function __construct(
        private TypeNameIdentifier $typeName,
        private array $enumerationValues
    ) {}

    /** @return array<string, EnumerationValue> */
    public function values(): array {
        return $this->enumerationValues;
    }

	/** @throws UnknownEnumerationValue **/
	public function value(EnumValueIdentifier $valueIdentifier): EnumerationValue {
		return $this->enumerationValues[$valueIdentifier->identifier] ??
			UnknownEnumerationValue::of($this->typeName, $valueIdentifier);
	}

    public function name(): TypeNameIdentifier {
        return $this->typeName;
    }

    public function isSubtypeOf(Type $ofType): bool {
		return match (true) {
			$ofType instanceof EnumerationTypeInterface => $this->typeName->equals($ofType->name()),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

    /**
     * @param list<EnumValueIdentifier> $values
     * @throws UnknownEnumerationValue|InvalidArgumentException
     **/
    public function subsetType(array $values): EnumerationSubsetTypeInterface {
	    if ($values === []) {
            throw new InvalidArgumentException("Cannot create an empty subset type");
        }
        $selected = [];
        foreach($values as $value) {
            $v = $this->enumerationValues[$value->identifier] ?? null;
            if ($v === null) {
				UnknownEnumerationValue::of($this->typeName, $value);
            }
            $selected[$value->identifier] = $v;
        }
        return count($selected) === count($this->enumerationValues) ?
            $this : new EnumerationSubsetType($this, $selected);
    }

    public function enumeration(): EnumerationType {
        return $this;
    }

    /** @return list<EnumerationValue> */
    public function subsetValues(): array {
        return $this->enumerationValues;
    }

	public function __toString(): string {
		return (string)$this->typeName;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Enumeration', 'name' => (string)$this->typeName, 'values' => $this->enumerationValues];
	}
}