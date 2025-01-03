<?php

namespace Walnut\Lang\Blueprint\Program;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final class UnknownType extends InvalidArgumentException {
    public function __construct(
        public readonly string $typeName
    ) {
        parent::__construct("Unknown type: '$typeName'");
    }

    public static function withName(TypeNameIdentifier $typeName): never {
        throw new self($typeName->identifier);
    }
}