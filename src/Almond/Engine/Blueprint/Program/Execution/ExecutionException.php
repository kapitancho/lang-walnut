<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Execution;

use Exception;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyError;

final class ExecutionException extends Exception {
	public function __construct(string $message, public readonly DependencyError|null $relatedError = null) {
		parent::__construct($message);
	}
}