<?php

namespace Walnut\Lang\Implementation\Type;

use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType as EnumerationSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;

final class EnumerationType implements EnumerationTypeInterface, JsonSerializable {

    /**
     * @param array<string, EnumerationValue> $values
     */
    public function __construct(
        public readonly TypeNameIdentifier $name,
        public readonly array               $values
    ) {}

	/** @throws UnknownEnumerationValue **/
	public function value(EnumValueIdentifier $valueIdentifier): EnumerationValue {
		return $this->values[$valueIdentifier->identifier] ??
			UnknownEnumerationValue::of($this->name, $valueIdentifier);
	}

    public function isSubtypeOf(Type $ofType): bool {
		return match (true) {
			$ofType instanceof EnumerationTypeInterface => $this->name->equals($ofType->name),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

    /**
     * @param list<EnumValueIdentifier> $values
     * @throws UnknownEnumerationValue|DuplicateSubsetValue|InvalidArgumentException
     **/
    public function subsetType(array $values): EnumerationSubsetTypeInterface {
	    if ($values === []) {
            throw new InvalidArgumentException("Cannot create an empty subset type");
        }
        $selected = [];
        foreach($values as $value) {
			if (array_key_exists($value->identifier, $selected)) {
				DuplicateSubsetValue::ofEnumeration(
					sprintf("%s[%s]", $this->name, implode(', ', $values)),
					$value
				);
			}
            $v = $this->values[$value->identifier] ?? null;
            if ($v === null) {
				UnknownEnumerationValue::of($this->name, $value);
            }
            $selected[$value->identifier] = $v;
        }
        return count($selected) === count($this->values) ?
            $this : new EnumerationSubsetType($this, $selected);
    }

	public EnumerationType $enumeration {
		get {
			return $this;
		}
	}

	/** @param array<string, EnumerationValue> $subsetValues */
	public array $subsetValues {
		get {
			return $this->values;
		}
	}

	public function __toString(): string {
		return (string)$this->name;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Enumeration', 'name' => (string)$this->name, 'values' => $this->values];
	}
}