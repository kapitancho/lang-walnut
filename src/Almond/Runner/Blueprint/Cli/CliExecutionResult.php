<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Cli;

use Walnut\Lang\Almond\Runner\Blueprint\Compilation\CompiledProgram;

interface CliExecutionResult extends CompiledProgram {
	public string $returnValue { get; }
}