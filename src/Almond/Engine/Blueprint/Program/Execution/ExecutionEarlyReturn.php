<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Execution;

use Exception;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

final class ExecutionEarlyReturn extends Exception {
	public function __construct(
		public readonly Value $returnValue
	) {
		parent::__construct();
	}
}