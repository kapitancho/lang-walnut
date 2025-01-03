<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Value\EnumerationValue as EnumerationValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class EnumerationValue implements EnumerationValueInterface, JsonSerializable {

    public function __construct(
        private readonly TypeRegistry $typeRegistry,
        private readonly TypeNameIdentifier $typeName,
        public readonly EnumValueIdentifier $name
    ) {}

	public EnumerationSubsetType $type {
        get => $this->enumeration->subsetType([$this->name]);
    }

	public EnumerationType $enumeration {
        get => $this->typeRegistry->enumeration($this->typeName);
    }

	public function equals(Value $other): bool {
		return $other instanceof EnumerationValueInterface &&
			$this->typeName->equals($other->enumeration->name) &&
			$this->name->equals($other->name);
	}

	public function __toString(): string {
		return sprintf(
			"%s.%s",
			$this->typeName,
			$this->name
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'EnumerationValue',
			'typeName' => $this->typeName,
			'valueIdentifier' => $this->name
		];
	}
}