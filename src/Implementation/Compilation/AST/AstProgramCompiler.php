<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstModuleCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstModuleCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompiler as AstProgramCompilerInterface;

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