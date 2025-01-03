<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode as ModuleNodeInterface;

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

	public function jsonSerialize(): array {
		return [
			'moduleName' => $this->moduleName,
			'moduleDependencies' => $this->moduleDependencies,
			'definitions' => $this->definitions
		];
	}
}