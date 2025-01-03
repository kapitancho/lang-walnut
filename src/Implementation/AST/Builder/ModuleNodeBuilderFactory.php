<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilderFactory as ModuleNodeBuilderFactoryInterface;

final readonly class ModuleNodeBuilderFactory implements ModuleNodeBuilderFactoryInterface {
	public function newBuilder(): ModuleNodeBuilder {
		return new ModuleNodeBuilder();
	}
}
