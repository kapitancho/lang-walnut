<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator\NodeIteratorFactory;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest as PreBuildValidationRequestInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess as PreBuildValidationSuccessInterface;

final class PreBuildValidationRequest implements PreBuildValidationRequestInterface {
	public function __construct(
		public readonly RootNode $rootNode,
		public readonly NodeIteratorFactory $nodeIteratorFactory,
	) {}

	public PreBuildValidationSuccessInterface $result {
		get => new PreBuildValidationSuccess();
	}

}