<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleDefinitionNode as ModuleDefinitionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

abstract readonly class ModuleDefinitionNode implements ModuleDefinitionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation
	) {}
}
