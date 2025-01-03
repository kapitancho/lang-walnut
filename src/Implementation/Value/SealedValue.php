<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Value\SealedValue as SealedValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class SealedValue implements SealedValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly TypeNameIdentifier $typeName,
	    public readonly RecordValue $value
    ) {}

	public SealedType $type {
        get => $this->typeRegistry->sealed($this->typeName);
    }

	public function equals(Value $other): bool {
		return $other instanceof SealedValueInterface &&
			$this->typeName->equals($other->type->name) &&
			$this->value->equals($other->value);
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