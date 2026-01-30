<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;

interface PreBuildValidationRequestFactory {
	public function newRequest(
		RootNode $rootNode,
	): PreBuildValidationRequest;
}