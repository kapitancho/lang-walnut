<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;

interface ProgramEntryPoint {
	/** @throws ExecutionException */
	public function call(Value $parameter): Value;
}