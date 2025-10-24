<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

interface AstCompilerFactory {
	public AstProgramCompiler $programCompiler { get; }
	public AstCodeMapper $astCodeMapper { get; }
}