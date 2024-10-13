<?php

namespace Walnut\Lang\Blueprint\Program\DependencyContainer;

use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface DependencyContainer {
	public function valueByType(Type $type): Value|DependencyError;
}