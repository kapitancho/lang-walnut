<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Program\EntryPoint\EntryPointProvider;
use Walnut\Lang\Blueprint\Program\ProgramContext;
use Walnut\Lang\Blueprint\Program\Program;

interface SuccessfulCompilationResult extends CompilationResult, EntryPointProvider {
	public ProgramContext $programContext { get; }
	public RootNode $ast { get; }
	public Program $program { get; }
	public null $errorState { get; }
}