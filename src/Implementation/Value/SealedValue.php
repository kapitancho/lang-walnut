<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Value\SealedValue as SealedValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class SealedValue implements SealedValueInterface, JsonSerializable {

    public function __construct(
		private TypeRegistry $typeRegistry,
		private TypeNameIdentifier $typeName,
	    private RecordValue $value
    ) {}

    public function type(): SealedType {
        return $this->typeRegistry->sealed($this->typeName);
    }

    public function value(): RecordValue {
		return $this->value;
    }

	public function equals(Value $other): bool {
		return $other instanceof SealedValueInterface &&
			$this->typeName->equals($other->type()->name()) &&
			$this->value->equals($other->value());
	}

	public function __toString(): string {
		$sv = (string)$this->value;
		return sprintf(
			str_starts_with($sv, '[') ? "%s%s" : "%s{%s}",
			$this->typeName,
			$sv
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'State',
			'typeName' => $this->typeName,
			'value' => $this->value
		];
	}
}