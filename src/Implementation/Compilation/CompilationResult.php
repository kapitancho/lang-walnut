<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Blueprint\Compilation\CompilationResult as CompilationResultInterface;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Program\ProgramAnalyserException;
use Walnut\Lang\Blueprint\Program\ProgramContext;
use Walnut\Lang\Blueprint\Program\Program;

final readonly class CompilationResult implements CompilationResultInterface {
	public function __construct(
		public RootNode|ModuleDependencyException|ParserException $ast,
		public Program|AstProgramCompilationException|ProgramAnalyserException|null $program,
		public ProgramContext $programContext
	) {}
}