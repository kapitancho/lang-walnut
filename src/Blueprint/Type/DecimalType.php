<?php /** @noinspection PhpUnused */ //TODO - implement

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\DecimalPrecision;
use Walnut\Lang\Blueprint\Range\RealRange;

interface DecimalType extends Type {
    public function range(): RealRange;
    public function precision(): DecimalPrecision;
}