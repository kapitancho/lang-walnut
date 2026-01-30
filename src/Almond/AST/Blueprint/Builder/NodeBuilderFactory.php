<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

interface NodeBuilderFactory {
	public function newBuilder(
		SourceLocator $sourceLocator
	): NodeBuilder;
}