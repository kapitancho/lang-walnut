<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Program\ProgramAnalyserException;
use Walnut\Lang\Blueprint\Program\ProgramContext;
use Walnut\Lang\Blueprint\Program\Program;

interface CompilationResult {
	public RootNode|ModuleDependencyException|ParserException $ast { get; }
	public Program|AstProgramCompilationException|AstCompilationException|ProgramAnalyserException|null $program { get; }
	public ProgramContext $programContext { get; }
}