<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

interface NodeBuilderFactory {
	public function newBuilder(
		SourceLocator $sourceLocator
	): NodeBuilder;
}