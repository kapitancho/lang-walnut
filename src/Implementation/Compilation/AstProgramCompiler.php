<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Compiler\AstModuleCompiler;
use Walnut\Lang\Blueprint\AST\Compiler\AstProgramCompiler as AstProgramCompilerInterface;
use Walnut\Lang\Blueprint\AST\Compiler\AstModuleCompilationException;
use Walnut\Lang\Blueprint\AST\Compiler\AstProgramCompilationException;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\RootNode;

final readonly class AstProgramCompiler implements AstProgramCompilerInterface {
	public function __construct(
		private AstModuleCompiler $astModuleCompiler
	) {}

	/** @throws AstProgramCompilationException */
	public function compileProgram(RootNode $root): void {
		$exceptions = array();
		array_map(function(ModuleNode $module) use (&$exceptions) {
			try {
				$this->astModuleCompiler->compileModule($module);
			} catch (AstModuleCompilationException $e) {
				$exceptions[] = $e;
			}
		}, $root->modules);

		if (count($exceptions) > 0) {
			throw new AstProgramCompilationException($exceptions);
		}
	}

}