<?php

namespace Walnut\Lang\Blueprint\Program;

use Walnut\Lang\Blueprint\Value\Value;

interface ProgramEntryPoint {
	public function call(Value $parameter): Value;
}