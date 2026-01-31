<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

interface UserlandMethodValidator {

	public function validateAll(): ValidationResult;
	public function validateAllDependencies(): DependencyContext;
}