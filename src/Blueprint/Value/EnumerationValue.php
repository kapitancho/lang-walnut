<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;

interface EnumerationValue extends Value {
    public function enumeration(): EnumerationType;
    public function name(): EnumValueIdentifier;
    public function type(): EnumerationSubsetType;
}