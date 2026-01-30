<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface ProgramEntryPoint {
	public function call(Value $parameter): Value;
}