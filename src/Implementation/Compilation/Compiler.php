<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Blueprint\Compilation\CompilationResult as CompilationResultInterface;
use Walnut\Lang\Blueprint\Compilation\Compiler as CompilerInterface;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Implementation\AST\Parser\NodeImporter;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\Compilation\AST\AstCompilerFactory;
use Walnut\Lang\Implementation\Compilation\AST\AstProgramCompiler;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;

final readonly class Compiler implements CompilerInterface {
	private NodeImporter $nodeImporter;
	private ProgramContextFactory $programContextFactory;
	public TransitionLogger $transitionLogger;
	public function __construct(
		private ModuleLookupContext $moduleLookupContext,
	) {
		$this->nodeImporter = new NodeImporter($this->transitionLogger = new TransitionLogger());
		$this->programContextFactory = new ProgramContextFactory();
	}

	private function getAstCompiler(ProgramContextInterface $programContext): AstProgramCompiler {
		return new AstCompilerFactory($programContext)->programCompiler;
	}

	public function safeCompile(string $source): CompilationResultInterface {
		$programContext = $this->programContextFactory->programContext;
		try {
			/// Part 1 - parser and import as AST
			$rootNode = $this->nodeImporter->importFromSource(
				$source,
				$this->moduleLookupContext
			);
		} catch (ModuleDependencyException|ParserException $e) {
			return new CompilationResult(
				$e,
				null,
				$programContext
			);
		}
		$astCompiler = $this->getAstCompiler($programContext);
		try {
			$astCompiler->compileProgram($rootNode);
		} catch (AstProgramCompilationException $e) {
			return new CompilationResult(
				$rootNode,
				$e,
				$programContext
			);
		}
		try {
			$program = $programContext->analyseAndBuildProgram();
		} catch (AnalyserException|AstCompilationException $e) {
			return new CompilationResult(
				$rootNode,
				$e,
				$programContext
			);
		}
		return new SuccessfulCompilationResult(
			$rootNode,
			$program,
			$programContext
		);
	}

	public function compile(string $source): SuccessfulCompilationResult {
		$this->getAstCompiler(
			$programContext = $this->programContextFactory->programContext
		)->compileProgram(
				$rootNode = $this->nodeImporter->importFromSource(
					$source,
					$this->moduleLookupContext
				)
			);
		$program = $programContext->analyseAndBuildProgram();
		return new SuccessfulCompilationResult(
			$rootNode,
			$program,
			$programContext
		);
	}
}