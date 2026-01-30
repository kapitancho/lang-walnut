<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator\NodeIteratorFactory;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequestFactory as PreBuildValidationRequestFactoryInterface;

final readonly class PreBuildValidationRequestFactory implements PreBuildValidationRequestFactoryInterface {
	public function __construct(
		private NodeIteratorFactory $nodeIteratorFactory
	) {}

	public function newRequest(RootNode $rootNode,): PreBuildValidationRequest {
		return new PreBuildValidationRequest(
			$rootNode,
			$this->nodeIteratorFactory,
		);
	}

}