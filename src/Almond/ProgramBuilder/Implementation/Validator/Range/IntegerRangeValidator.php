<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntegerTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class IntegerRangeValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			IntegerTypeNode::class
		) as $integerTypeNode) {
			if (
				$integerTypeNode->maxValue !== PlusInfinity::value &&
				$integerTypeNode->minValue !== MinusInfinity::value &&
				$integerTypeNode->minValue > $integerTypeNode->maxValue
			) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::invalidRange,
					sprintf("Integer range (%s..%s) is not valid",
						$integerTypeNode->minValue, $integerTypeNode->maxValue),
					[$integerTypeNode]
				);
			}
		}

		return $result;
	}
}
