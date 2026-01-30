<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

interface UserlandMethodValidator {

	public function validateAll(): ValidationResult;
	public function validateAllDependencies(): DependencyContext;
}