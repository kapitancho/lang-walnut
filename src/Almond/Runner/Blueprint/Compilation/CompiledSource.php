<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Compilation;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramSource;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;

interface CompiledSource extends CompilationResult {
	public ProgramSource $programSource { get; }
	public RootNode $rootNode { get; }

	public function asCompiledProgram(): CompiledProgram|CompilationFailure;
}