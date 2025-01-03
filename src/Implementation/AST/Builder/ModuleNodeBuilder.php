<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilder as ModuleNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Implementation\AST\Node\Module\ModuleNode;

final class ModuleNodeBuilder implements ModuleNodeBuilderInterface {
	private string $moduleName;
	/** @param list<string> $moduleDependencies */
	private array $moduleDependencies = [];
	/** @param list<ModuleDefinitionNode> $definitions */
	private array $definitions = [];

	public function __construct(
	) {}

	public function moduleName(string $moduleName): ModuleNodeBuilderInterface {
		$this->moduleName = $moduleName;
		return $this;
	}

	public function moduleDependencies(array $dependencies): ModuleNodeBuilderInterface {
		$this->moduleDependencies = $dependencies;
		return $this;
	}

	public function definition(ModuleDefinitionNode $definition): ModuleNodeBuilderInterface {
		$this->definitions[] = $definition;
		return $this;
	}

	public function build(): ModuleNode {
		return new ModuleNode(
			$this->moduleName,
			$this->moduleDependencies,
			$this->definitions
		);
	}
}