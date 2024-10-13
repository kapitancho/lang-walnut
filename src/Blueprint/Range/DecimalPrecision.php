<?php /** @noinspection PhpUnused */ //TODO - implement

namespace Walnut\Lang\Blueprint\Range;

use Stringable;

interface DecimalPrecision extends Stringable {
    public function value(): int;
}