<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface UnknownProperty {
	public string|int $propertyName { get; }
	public Type|Value $notFoundIn { get; }
}