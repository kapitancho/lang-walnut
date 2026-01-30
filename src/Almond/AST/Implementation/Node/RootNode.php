<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode as RootNodeInterface;

final readonly class RootNode implements RootNodeInterface {
	/** @param list<ModuleNode> $modules */
	public function __construct(
		public string $startModuleName,
		public array $modules
	) {}

	/** @return iterable<Node> */
	public function children(): iterable {
		yield from $this->modules;
	}

	public function jsonSerialize(): array {
		return [
			'startModuleName' => $this->startModuleName,
			'modules' => $this->modules
		];
	}
}