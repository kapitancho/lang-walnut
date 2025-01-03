<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

interface ModuleNodeBuilderFactory {
	public function newBuilder(): ModuleNodeBuilder;
}
