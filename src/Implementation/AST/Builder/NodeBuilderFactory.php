<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory as NodeBuilderFactoryInterface;
use Walnut\Lang\Blueprint\AST\Builder\SourceLocator;

final readonly class NodeBuilderFactory implements NodeBuilderFactoryInterface {

	public function newBuilder(
		SourceLocator $sourceLocator
	): NodeBuilder {
		return new NodeBuilder($sourceLocator);
	}
}