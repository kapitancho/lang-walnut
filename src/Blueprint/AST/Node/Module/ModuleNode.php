<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Node;

interface ModuleNode extends Node {
	public string $moduleName { get; }
	/** @var list<string> */
	public array $moduleDependencies { get; }
	/** @var list<ModuleDefinitionNode> */
	public array $definitions { get; }
}