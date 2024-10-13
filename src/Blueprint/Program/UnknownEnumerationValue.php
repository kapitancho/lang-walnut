<?php

namespace Walnut\Lang\Blueprint\Program;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

final class UnknownEnumerationValue extends InvalidArgumentException {
    public function __construct(
        public readonly string $typeName,
        public readonly string $valueName,
    ) {
        parent::__construct(
            sprintf(
                "Unknown enumeration value: '%s' for type '%s'",
                $valueName, $typeName)
        );
    }

    public static function of(
        TypeNameIdentifier $typeName,
        EnumValueIdentifier $enumValue
    ): never {
        throw new self($typeName->identifier, $enumValue->identifier);
    }
}