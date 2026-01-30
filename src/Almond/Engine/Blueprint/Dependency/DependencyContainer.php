<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Dependency;

use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface DependencyContainer {
	public function checkForType(Type $type, UserlandFunction $origin): ValidationError|null;
	public function valueForType(Type $type): Value|DependencyError;
}