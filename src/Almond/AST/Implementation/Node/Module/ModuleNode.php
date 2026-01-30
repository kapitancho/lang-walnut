<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode as ModuleNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;

final readonly class ModuleNode implements ModuleNodeInterface {
	/**
	 * @param list<string> $moduleDependencies
	 * @param list<ModuleDefinitionNode> $definitions
	 */
	public function __construct(
		public string $moduleName,
		public array $moduleDependencies,
		public array $definitions
	) {}

	/** @return iterable<Node> */
	public function children(): iterable {
		yield from $this->definitions;
	}

	public function jsonSerialize(): array {
		return [
			'moduleName' => $this->moduleName,
			'moduleDependencies' => $this->moduleDependencies,
			'definitions' => $this->definitions
		];
	}
}