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
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry as ProgramRegistryInterface;
use Walnut\Lang\Implementation\AST\Parser\NodeImporter;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Compilation\AST\AstCompilerFactory;
use Walnut\Lang\Implementation\Compilation\AST\AstProgramCompiler;
use Walnut\Lang\Implementation\Function\CustomMethodAnalyser;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Program;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;

final readonly class Compiler implements CompilerInterface {
	private const string lookupNamespace = 'Walnut\\Lang\\NativeCode';

	private NodeImporter $nodeImporter;
	public TransitionLogger $transitionLogger;
	public function __construct(
		private ModuleLookupContext $moduleLookupContext,
	) {
		$this->nodeImporter = new NodeImporter($this->transitionLogger = new TransitionLogger());
	}

	private function getAstCompiler(CompilationContextInterface $compilationContext): AstProgramCompiler {
		return new AstCompilerFactory($compilationContext)->programCompiler;
	}

	private function analyseProgram(CompilationContextInterface $compilationContext): ProgramRegistryInterface {
		$customMethodRegistryBuilder = new CustomMethodRegistryBuilder();
		$customMethodRegistry = $customMethodRegistryBuilder->buildFromDrafts(
			$compilationContext->customMethodDraftRegistryBuilder);

		$nativeCodeTypeMapper = new NativeCodeTypeMapper();
		$methodRegistry = new MainMethodRegistry(
			$nativeCodeTypeMapper,
			$customMethodRegistry,
			[
				self::lookupNamespace
			]
		);
		$programRegistry = new ProgramRegistry(
			$compilationContext->typeRegistry,
			$compilationContext->valueRegistry,
			$methodRegistry,
			$compilationContext->globalScopeBuilder,
			$compilationContext->expressionRegistry
		);

		$customMethodAnalyser = new CustomMethodAnalyser(
			$programRegistry,
			$compilationContext->typeRegistry,
			$methodRegistry,
			$programRegistry->dependencyContainer
		);
		$analyseErrors = $customMethodAnalyser->analyse($customMethodRegistry);
		if (count($analyseErrors) > 0) {
			throw new AnalyserException(implode("\n", $analyseErrors));
		}
		return $programRegistry;
	}

	public function safeCompile(string $source): CompilationResultInterface {
		$compilationContext = new CompilationContext();
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
			$programRegistry = $this->analyseProgram($compilationContext);
		} catch (AnalyserException|AstCompilationException $e) {
			return new CompilationResult(
				$rootNode,
				$e,
				$compilationContext
			);
		}
		$program = new Program(
			$programRegistry,
			$compilationContext->globalScopeBuilder->build(),
		);
		return new SuccessfulCompilationResult(
			$rootNode,
			$program,
			$compilationContext
		);
	}

	public function compile(string $source): SuccessfulCompilationResult {
		$compilationContext = new CompilationContext();
		$astCompiler = $this->getAstCompiler($compilationContext);
		$rootNode = $this->nodeImporter->importFromSource(
			$source,
			$this->moduleLookupContext
		);
		$astCompiler->compileProgram($rootNode);
		$programRegistry = $this->analyseProgram($compilationContext);
		$program = new Program(
			$programRegistry,
			$compilationContext->globalScopeBuilder->build(),
		);

		return new SuccessfulCompilationResult(
			$rootNode,
			$program,
			$compilationContext
		);
	}
}