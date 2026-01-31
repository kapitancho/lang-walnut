<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError;

interface DependencyContainer {
	public function checkForType(Type $type, UserlandFunction $origin): ValidationError|null;
	public function valueForType(Type $type): Value|DependencyError;
}