<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\Compiler as CompilerInterface;
use Walnut\Lang\Blueprint\Compilation\CompilationResult;
use Walnut\Lang\Blueprint\Compilation\ModuleLookupContext;
use Walnut\Lang\Implementation\Compilation\Parser\Parser;
use Walnut\Lang\Implementation\Compilation\Parser\TransitionLogger;
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

	public function compile(string $source): CompilationResult {
		$pf = new ProgramFactory();
		$codeBuilder = $pf->codeBuilder;
		$moduleImporter = new ModuleImporter(
			$this->lexer,
			$this->moduleLookupContext,
			$this->parser,
			$codeBuilder
		);
		$moduleImporter->importModule($source);
		return new CompilationResult(
			$pf->builder->analyseAndBuildProgram(),
			$pf->registry
		);
	}
}