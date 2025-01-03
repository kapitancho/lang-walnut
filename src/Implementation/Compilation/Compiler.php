<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Compiler\AstProgramCompilationException;
use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Compilation\CompilationResult as CompilationResultInterface;
use Walnut\Lang\Blueprint\Compilation\Compiler as CompilerInterface;
use Walnut\Lang\Blueprint\Compilation\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\ModuleLookupContext;
use Walnut\Lang\Implementation\AST\Builder\ModuleNodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Parser\Parser;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\AST\Parser\WalexLexerAdapter;
use Walnut\Lang\Implementation\Program\Factory\ProgramFactory;

final readonly class Compiler implements CompilerInterface {
	private WalexLexerAdapter $lexer;
	private Parser $parser;
	public TransitionLogger $transitionLogger;
	public function __construct(
		private ModuleLookupContext $moduleLookupContext
	) {
		$this->lexer = new WalexLexerAdapter();
		$this->transitionLogger = new TransitionLogger();
		$this->parser = new Parser($this->transitionLogger);
	}

	private function getProgramFactory(): ProgramFactory {
		return new ProgramFactory();
	}

	private function getModuleImporter(ProgramFactory $pf): ModuleImporter {
		return new ModuleImporter(
			$this->lexer,
			$this->moduleLookupContext,
			$this->parser,
			$pf->nodeBuilderFactory,
			new ModuleNodeBuilderFactory
		);
	}

	/** @throws ModuleDependencyException|ParserException */
	private function importModules(ProgramFactory $pf, string $source): RootNode {
		return $this->getModuleImporter($pf)->importModules($source);
	}

	/** @throws AstProgramCompilationException */
	private function compileAst(ProgramFactory $pf, RootNode $rootNode): void {
		$astCompiler = new AstCompiler($pf->codeBuilder);
		$astCompiler->compile($rootNode);
	}

	/** @throws ModuleDependencyException|AstProgramCompilationException|AnalyserException|ParserException */
	public function compile(string $source): SuccessfulCompilationResult {
		$pf = $this->getProgramFactory();
		$this->compileAst($pf, $rootNode = $this->importModules($pf, $source));
		return new SuccessfulCompilationResult(
			$rootNode,
			$pf->builder->analyseAndBuildProgram(),
			$pf->registry
		);
	}

	public function safeCompile(string $source): CompilationResultInterface {
		$pf = $this->getProgramFactory();
		try {
			$rootNode = $this->importModules($pf, $source);
		} catch (ModuleDependencyException|ParserException $e) {
			return new CompilationResult(
				$e,
				null,
				$pf->registry
			);
		}
		try {
			$this->compileAst($pf, $rootNode);
		} catch (AstProgramCompilationException $e) {
			return new CompilationResult(
				$rootNode,
				$e,
				$pf->registry
			);
		}
		try {
			$program = $pf->builder->analyseAndBuildProgram();
		} catch (AnalyserException $e) {
			return new CompilationResult(
				$rootNode,
				$e,
				$pf->registry
			);
		}

		return new SuccessfulCompilationResult(
			$rootNode,
			$program,
			$pf->registry
		);
	}
}