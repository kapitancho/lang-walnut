<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Value\EnumerationValue as EnumerationValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class EnumerationValue implements EnumerationValueInterface, JsonSerializable {

    public function __construct(
        private TypeRegistry $typeRegistry,
        private TypeNameIdentifier $typeName,
        private EnumValueIdentifier $valueIdentifier
    ) {}

    public function type(): EnumerationSubsetType {
        return $this->enumeration()->subsetType([$this->valueIdentifier]);
    }

    public function enumeration(): EnumerationType {
        return $this->typeRegistry
            ->enumeration($this->typeName);
    }

    public function name(): EnumValueIdentifier {
        return $this->valueIdentifier;
    }

	public function equals(Value $other): bool {
		return $other instanceof EnumerationValueInterface &&
			$this->typeName->equals($other->enumeration()->name()) &&
			$this->valueIdentifier->equals($other->name());
	}

	public function __toString(): string {
		return sprintf(
			"%s.%s",
			$this->typeName,
			$this->valueIdentifier
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'EnumerationValue',
			'typeName' => $this->typeName,
			'valueIdentifier' => $this->valueIdentifier
		];
	}
}