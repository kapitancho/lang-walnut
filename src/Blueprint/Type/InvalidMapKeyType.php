<?php

namespace Walnut\Lang\Blueprint\Type;

use InvalidArgumentException;

final class InvalidMapKeyType extends InvalidArgumentException {
    public function __construct(
        public readonly string $type,
    ) {
        parent::__construct(
            sprintf(
                "The map key type '%s' must be a subset of the String type",
                $type)
        );
    }
}