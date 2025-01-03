<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

interface SuccessfulCompilationResult extends CompilationResult {
	public RootNode $ast { get; }
	public Program $program { get; }
	public ProgramRegistry $programRegistry { get; }
}