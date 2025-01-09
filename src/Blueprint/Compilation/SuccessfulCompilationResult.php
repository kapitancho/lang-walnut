<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Program\Program;

interface SuccessfulCompilationResult extends CompilationResult {
	public RootNode $ast { get; }
	public Program $program { get; }
	public CompilationContext $compilationContext { get; }
}