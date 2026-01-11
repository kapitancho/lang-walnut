<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Program\ProgramContext;

interface FailedCompilationResult extends CompilationResult {
	public ProgramContext $programContext { get; }
	public RootNode|null $ast { get; }
	public null $program { get; }
	public CompilationException $errorState { get; }
}