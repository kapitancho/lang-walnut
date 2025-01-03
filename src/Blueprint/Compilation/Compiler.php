<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Compiler\AstProgramCompilationException;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;

interface Compiler {
	public function safeCompile(string $source): CompilationResult;
	/** @throws ModuleDependencyException|AstProgramCompilationException|AnalyserException|ParserException */
	public function compile(string $source): SuccessfulCompilationResult;
}