<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;

interface EnumerationValue extends Value {
	public EnumerationType $enumeration { get; }
	public EnumerationValueName $name { get; }
}