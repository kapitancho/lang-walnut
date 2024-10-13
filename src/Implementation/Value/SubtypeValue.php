<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Value\SubtypeValue as SubtypeValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class SubtypeValue implements SubtypeValueInterface, JsonSerializable {

    public function __construct(
		private TypeRegistry $typeRegistry,
		private TypeNameIdentifier $typeName,
	    private Value $baseValue
    ) {}

    public function type(): SubtypeType {
        return $this->typeRegistry->subtype($this->typeName);
    }

    public function baseValue(): Value {
		return $this->baseValue;
    }

	public function equals(Value $other): bool {
		return $other instanceof SubtypeValueInterface &&
			$this->typeName->equals($other->type()->name()) &&
			$this->baseValue->equals($other->baseValue());
	}

	public function __toString(): string {
		$bv = (string)$this->baseValue;
		return sprintf(
			str_starts_with($bv, '[') ? "%s%s" : "%s{%s}",
			$this->typeName,
			$bv
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Subtype',
			'typeName' => $this->typeName,
			'baseValue' => $this->baseValue
		];
	}
}