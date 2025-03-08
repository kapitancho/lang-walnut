<?php

namespace Walnut\Lang\Blueprint\Code\Execution;

use RuntimeException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Value\Value;

class FunctionReturn extends RuntimeException {
	public function __construct(public readonly TypedValue $typedValue) {
		parent::__construct();
	}
}
