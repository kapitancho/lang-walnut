<?php

namespace Walnut\Lang\Blueprint\Type;

interface SealedType extends NamedType {
    public function valueType(): RecordType;
}