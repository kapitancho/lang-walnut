<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Value\SubtypeValue as SubtypeValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class SubtypeValue implements SubtypeValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly TypeNameIdentifier $typeName,
	    public readonly Value $baseValue
    ) {}

	public SubtypeType $type {
        get => $this->typeRegistry->subtype($this->typeName);
    }

	public function equals(Value $other): bool {
		return $other instanceof SubtypeValueInterface &&
			$this->typeName->equals($other->type->name) &&
			$this->baseValue->equals($other->baseValue);
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