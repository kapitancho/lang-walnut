<?php

namespace Walnut\Lang\Implementation\AST\Node;

use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\RootNode as RootNodeInterface;

final readonly class RootNode implements RootNodeInterface {
	/** @param list<ModuleNode> $modules */
	public function __construct(
		public string $startModuleName,
		public array $modules
	) {}

	public function jsonSerialize(): array {
		return [
			'startModuleName' => $this->startModuleName,
			'modules' => $this->modules
		];
	}
}