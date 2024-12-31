<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;

interface EnumerationValue extends Value {
	public EnumerationType $enumeration { get; }
	public EnumValueIdentifier $name { get; }
	public EnumerationSubsetType $type { get; }
}