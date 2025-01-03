<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use Walnut\Lang\Blueprint\AST\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;

interface ModuleNodeBuilder {
	public function moduleName(string $moduleName): self;
	/** @param list<string> $dependencies */
	public function moduleDependencies(array $dependencies): self;
	public function definition(ModuleDefinitionNode $definition): self;

	public function build(): ModuleNode;
}
