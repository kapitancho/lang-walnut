<?php

namespace Walnut\Lang\Blueprint\AST\Node;

use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;

interface RootNode extends Node {
	public string $startModuleName { get; }
	/** @var array<string, ModuleNode> */
	public array $modules { get; }
}