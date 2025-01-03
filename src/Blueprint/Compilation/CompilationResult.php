<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Compiler\AstProgramCompilationException;
use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

interface CompilationResult {
	public RootNode|ModuleDependencyException|ParserException $ast { get; }
	public Program|AstProgramCompilationException|AnalyserException|null $program { get; }
	public ProgramRegistry $programRegistry { get; }
}