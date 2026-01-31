<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Range;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class NumberIntervalValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			NumberIntervalNode::class
		) as $numberIntervalNode) {
			if (
				$numberIntervalNode->end !== PlusInfinity::value &&
				$numberIntervalNode->start !== MinusInfinity::value &&
				$numberIntervalNode->start > $numberIntervalNode->end
			) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::invalidRange,
					"Number interval ({$numberIntervalNode->start->value}..{$numberIntervalNode->end->value}) is not valid",
					[$numberIntervalNode]
				);
			}
		}

		return $result;
	}
}
