<?php

namespace Walnut\Lang\Blueprint\Program\DependencyContainer;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Type\Type;

interface DependencyContainer {
	public function valueByType(Type $type): TypedValue|DependencyError;
}