<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Blueprint\Compilation\CompilationContext as CompilationContextInterface;
use Walnut\Lang\Blueprint\Compilation\CompilationResult as CompilationResultInterface;
use Walnut\Lang\Blueprint\Compilation\Compiler as CompilerInterface;
use Walnut\Lang\Blueprint\Compilation\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\ModuleLookupContext;
use Walnut\Lang\Implementation\AST\Parser\NodeImporter;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\Compilation\AST\AstCompilerFactory;
use Walnut\Lang\Implementation\Compilation\AST\AstProgramCompiler;

final readonly class Compiler implements CompilerInterface {
	private NodeImporter $nodeImporter;
	private CompilationContextFactory $compilationContextFactory;
	public TransitionLogger $transitionLogger;
	public function __construct(
		private ModuleLookupContext $moduleLookupContext,
	) {
		$this->nodeImporter = new NodeImporter($this->transitionLogger = new TransitionLogger());
		$this->compilationContextFactory = new CompilationContextFactory();
	}

	private function getAstCompiler(CompilationContextInterface $compilationContext): AstProgramCompiler {
		return new AstCompilerFactory($compilationContext)->programCompiler;
	}

	public function safeCompile(string $source): CompilationResultInterface {
		$compilationContext = $this->compilationContextFactory->compilationContext;
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
				$compilationContext
			);
		}
		$astCompiler = $this->getAstCompiler($compilationContext);
		try {
			$astCompiler->compileProgram($rootNode);
		} catch (AstProgramCompilationException $e) {
			return new CompilationResult(
				$rootNode,
				$e,
				$compilationContext
			);
		}
		try {
			$program = $compilationContext->analyseAndBuildProgram();
		} catch (AnalyserException|AstCompilationException $e) {
			return new CompilationResult(
				$rootNode,
				$e,
				$compilationContext
			);
		}
		return new SuccessfulCompilationResult(
			$rootNode,
			$program,
			$compilationContext
		);
	}

	public function compile(string $source): SuccessfulCompilationResult {
		$this->getAstCompiler(
			$compilationContext = $this->compilationContextFactory->compilationContext
		)->compileProgram(
				$rootNode = $this->nodeImporter->importFromSource(
					$source,
					$this->moduleLookupContext
				)
			);
		$program = $compilationContext->analyseAndBuildProgram();
		return new SuccessfulCompilationResult(
			$rootNode,
			$program,
			$compilationContext
		);
	}
}