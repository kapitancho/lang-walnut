<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RealTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class RealRangeValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			RealTypeNode::class
		) as $realTypeNode) {
			if (
				$realTypeNode->maxValue !== PlusInfinity::value &&
				$realTypeNode->minValue !== MinusInfinity::value &&
				$realTypeNode->minValue > $realTypeNode->maxValue
			) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::invalidRange,
					sprintf("Real range (%s..%s) is not valid",
						$realTypeNode->minValue, $realTypeNode->maxValue),
					[$realTypeNode]
				);
			}
		}

		return $result;
	}
}
