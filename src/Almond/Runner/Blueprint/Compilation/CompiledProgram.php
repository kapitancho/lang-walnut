<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Compilation;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Program;

interface CompiledProgram extends CompilationResult {
	public Program $program { get; }
	public RootNode $rootNode { get; }
}