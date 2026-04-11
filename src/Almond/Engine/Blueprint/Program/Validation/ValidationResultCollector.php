<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Validation;

use Walnut\Lang\Almond\Engine\Implementation\Program\Validation\ValidationContext;

interface ValidationResultCollector {
	public function collect(ValidationContext $param): void;
}