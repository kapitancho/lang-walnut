<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ModuleBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ProgramBuilder as ProgramCompilerInterface;

final readonly class ProgramBuilder implements ProgramCompilerInterface {
	public function __construct(
		private ModuleBuilder $astModuleCompiler
	) {}

	/** @throws ProgramCompilationException */
	public function compileProgram(RootNode $root): void {
		$exceptions = array();
		array_map(function(ModuleNode $module) use (&$exceptions) {
			try {
				$this->astModuleCompiler->compileModule($module);
			} catch (ModuleCompilationException $e) {
				$exceptions[] = $e;
			}
		}, $root->modules);

		if (count($exceptions) > 0) {
			throw new ProgramCompilationException($exceptions);
		}
	}

}