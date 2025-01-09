<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

interface AstCompilerFactory {
	public AstProgramCompiler $programCompiler { get; }
	/*public AstFunctionBodyCompiler $functionBodyCompiler { get; }
	public FunctionBodyBuilder $functionBodyBuilder { get; }
	public ProgramTypeBuilder $programTypeBuilder { get; }*/
}