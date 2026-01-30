<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;

interface RootNode extends Node {
	public string $startModuleName { get; }
	/** @var list<ModuleNode> */
	public array $modules { get; }
}