<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Node;

interface ModuleNode extends Node {
	public string $moduleName { get; }
	/** @var list<string> */
	public array $moduleDependencies { get; }
	/** @var list<ModuleDefinitionNode> */
	public array $definitions { get; }
}