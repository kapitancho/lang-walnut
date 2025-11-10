<?php

namespace Walnut\Lang\Blueprint\Program;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final class UnknownType extends InvalidArgumentException {
    public function __construct(
        public readonly string $typeName,
        public readonly string|null $prefix = null,
    ) {
        parent::__construct(
			sprintf(
				"Unknown%s type: '$typeName'", $prefix ? " $prefix" : '')
        );
    }

    public static function withName(TypeNameIdentifier $typeName, string|null $prefix = null): never {
        throw new self($typeName->identifier, $prefix);
    }
}