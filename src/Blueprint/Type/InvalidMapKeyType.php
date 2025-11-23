<?php

namespace Walnut\Lang\Blueprint\Type;

use InvalidArgumentException;

final class InvalidMapKeyType extends InvalidArgumentException {
    public function __construct(
        public readonly string $type,
    ) {
        parent::__construct(
            sprintf(
                "Invalid map key type: '%s'. The key type must be a subset of the String type",
                $type)
        );
    }
}