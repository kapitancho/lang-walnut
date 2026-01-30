<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator\NodeIteratorFactory;

interface PreBuildValidationRequest {
	public RootNode $rootNode { get; }
	public NodeIteratorFactory $nodeIteratorFactory { get; }

	public PreBuildValidationSuccess $result { get; }
}