<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\MapTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class MapLengthRangeValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			MapTypeNode::class
		) as $mapTypeNode) {
			if (
				$mapTypeNode->maxLength !== PlusInfinity::value &&
				$mapTypeNode->minLength > $mapTypeNode->maxLength
			) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::invalidRange,
					sprintf("Map length range (%s..%s) is not valid",
						$mapTypeNode->minLength, $mapTypeNode->maxLength),
					[$mapTypeNode]
				);
			}
		}

		return $result;
	}
}
