<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface UnknownProperty {
	public string|int $propertyName { get; }
	public Type|Value $notFoundIn { get; }
}