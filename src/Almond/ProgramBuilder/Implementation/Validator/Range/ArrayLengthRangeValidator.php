<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ArrayTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class ArrayLengthRangeValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			ArrayTypeNode::class
		) as $arrayTypeNode) {
			if (
				$arrayTypeNode->maxLength !== PlusInfinity::value &&
				$arrayTypeNode->minLength > $arrayTypeNode->maxLength
			) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::invalidRange,
					"Array length range ({$arrayTypeNode->minLength}..{$arrayTypeNode->maxLength}) is not valid",
					[$arrayTypeNode]
				);
			}
		}

		return $result;
	}
}
