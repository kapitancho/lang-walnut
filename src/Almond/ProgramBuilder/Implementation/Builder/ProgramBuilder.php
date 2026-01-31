<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ModuleBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ProgramBuilder as ProgramCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;

final readonly class ProgramBuilder implements ProgramCompilerInterface {
	public function __construct(
		private ModuleBuilder $astModuleCompiler
	) {}

	/** @throws BuildException */
	public function compileProgram(RootNode $root): void {
		array_map(function(ModuleNode $module) {
			$this->astModuleCompiler->compileModule($module);
		}, $root->modules);
	}

}