<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Program\EntryPoint\EntryPointProvider;
use Walnut\Lang\Blueprint\Program\ProgramContext;
use Walnut\Lang\Blueprint\Program\Program;

interface SuccessfulCompilationResult extends CompilationResult, EntryPointProvider {
	public RootNode $ast { get; }
	public Program $program { get; }
	public ProgramContext $programContext { get; }
}