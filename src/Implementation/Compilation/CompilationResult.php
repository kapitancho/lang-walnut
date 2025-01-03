<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Compiler\AstProgramCompilationException;
use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Compilation\CompilationResult as CompilationResultInterface;
use Walnut\Lang\Blueprint\Compilation\ModuleDependencyException;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

final readonly class CompilationResult implements CompilationResultInterface {
	public function __construct(
		public RootNode|ModuleDependencyException|ParserException $ast,
		public Program|AstProgramCompilationException|AnalyserException|null $program,
		public ProgramRegistry $programRegistry
	) {}
}