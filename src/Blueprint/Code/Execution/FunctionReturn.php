<?php

namespace Walnut\Lang\Blueprint\Code\Execution;

use RuntimeException;
use Walnut\Lang\Blueprint\Value\Value;

class FunctionReturn extends RuntimeException {
	public function __construct(public readonly Value $value) {
		parent::__construct();
	}
}
