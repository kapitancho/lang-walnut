<?php

namespace Walnut\Lang\Implementation\Type;

use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;

final class BooleanType implements BooleanTypeInterface, JsonSerializable {

	/** @var list<BooleanValue> $enumerationValues */
	public readonly array $values;
	private readonly TrueType $trueType;
	private readonly FalseType $falseType;

    public function __construct(
        public readonly TypeNameIdentifier $name,
        private readonly BooleanValue       $trueValue,
        private readonly BooleanValue       $falseValue
    ) {
		$this->values = [
			$this->trueValue->name->identifier => $this->trueValue,
	        $this->falseValue->name->identifier => $this->falseValue
        ];
		$this->trueType = new TrueType($this, $this->trueValue);
		$this->falseType = new FalseType($this, $this->falseValue);
    }

    public function isSubtypeOf(Type $ofType): bool {
        return $ofType instanceof BooleanTypeInterface || (
            $ofType instanceof SupertypeChecker &&
            $ofType->isSupertypeOf($this)
        );
    }

    /**
     * @param list<EnumerationValue> $values
     * @throws InvalidArgumentException
     **/
    public function subsetType(array $values): TrueType|FalseType|BooleanType {
		if ($values === []) {
	        throw new InvalidArgumentException("Cannot create an empty subset type");
	    }
	    $v = $values[0];
		if (count($values) === 1) {
			if ($v->identifier === $this->trueType->value->name->identifier) {
				return $this->trueType;
			}
			if ($v->identifier === $this->falseType->value->name->identifier) {
				return $this->falseType;
			}
		}
        foreach($values as $value) {
            $v = $this->values[$value->identifier] ?? null;
            if ($v === null) {
				UnknownEnumerationValue::of($this->name, $value);
            }
        }
		return $this;
    }

	/** @throws InvalidArgumentException **/
	public function value(EnumValueIdentifier $valueIdentifier): BooleanValue {
		return $this->values[$valueIdentifier->identifier] ??
			UnknownEnumerationValue::of($this->name, $valueIdentifier);
	}

	public BooleanTypeInterface $enumeration {
		get {
			return $this;
		}
	}

	/** @param list<BooleanValue> $subsetValues */
	public array $subsetValues {
		get {
			return $this->values;
		}
	}

	public function __toString(): string {
		return 'Boolean';
	}

	public function jsonSerialize(): array {
		return ['type' => 'Boolean'];
	}
}