<?php

namespace Walnut\Lang\Implementation\Type;

use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;

final readonly class BooleanType implements BooleanTypeInterface, JsonSerializable {

	/** @var list<BooleanValue> $enumerationValues */
	private array $enumerationValues;
	private TrueType $trueType;
	private FalseType $falseType;

    public function __construct(
        private TypeNameIdentifier $typeName,
        private BooleanValue $trueValue,
        private BooleanValue $falseValue
    ) {
		$this->enumerationValues = [
			$this->trueValue->name()->identifier => $this->trueValue,
	        $this->falseValue->name()->identifier => $this->falseValue
        ];
		$this->trueType = new TrueType($this, $this->trueValue);
		$this->falseType = new FalseType($this, $this->falseValue);
    }

    /** @return array<string, EnumerationValue> */
    public function values(): array {
        return $this->enumerationValues;
    }

    public function name(): TypeNameIdentifier {
        return $this->typeName;
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
			if ($v->identifier === $this->trueType->value()->name()->identifier) {
				return $this->trueType;
			}
			if ($v->identifier === $this->falseType->value()->name()->identifier) {
				return $this->falseType;
			}
		}
        foreach($values as $value) {
            $v = $this->enumerationValues[$value->identifier] ?? null;
            if ($v === null) {
				UnknownEnumerationValue::of($this->typeName, $value);
            }
        }
		return $this;
    }

	/** @throws InvalidArgumentException **/
	public function value(EnumValueIdentifier $valueIdentifier): BooleanValue {
		return $this->enumerationValues[$valueIdentifier->identifier] ??
			UnknownEnumerationValue::of($this->typeName, $valueIdentifier);
	}

    public function enumeration(): BooleanType {
        return $this;
    }

    /** @return list<BooleanValue> */
    public function subsetValues(): array {
        return $this->enumerationValues;
    }

	public function __toString(): string {
		return 'Boolean';
	}

	public function jsonSerialize(): array {
		return ['type' => 'Boolean'];
	}
}