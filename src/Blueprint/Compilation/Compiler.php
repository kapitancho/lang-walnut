<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;

interface Compiler {
	public function safeCompile(string $source): CompilationResult;
	/** @throws ModuleDependencyException|AstProgramCompilationException|AnalyserException|ParserException */
	public function compile(string $source): SuccessfulCompilationResult;
}