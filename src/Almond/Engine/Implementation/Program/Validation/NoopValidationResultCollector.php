<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResultCollector;

final class NoopValidationResultCollector implements ValidationResultCollector {
	public function collect(ValidationContext $param): void {}
}