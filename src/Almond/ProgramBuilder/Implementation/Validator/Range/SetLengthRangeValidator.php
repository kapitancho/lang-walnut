<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\SetTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class SetLengthRangeValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			SetTypeNode::class
		) as $setTypeNode) {
			if (
				$setTypeNode->maxLength !== PlusInfinity::value &&
				$setTypeNode->minLength > $setTypeNode->maxLength
			) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::invalidRange,
					sprintf("Set length range (%s..%s) is not valid",
						$setTypeNode->minLength, $setTypeNode->maxLength),
					[$setTypeNode]
				);
			}
		}

		return $result;
	}
}
