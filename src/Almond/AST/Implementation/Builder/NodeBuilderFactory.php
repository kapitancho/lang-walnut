<?php

namespace Walnut\Lang\Almond\AST\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Builder\NodeBuilderFactory as NodeBuilderFactoryInterface;
use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;

final readonly class NodeBuilderFactory implements NodeBuilderFactoryInterface {

	public function newBuilder(
		SourceLocator $sourceLocator
	): NodeBuilder {
		return new NodeBuilder($sourceLocator);
	}
}