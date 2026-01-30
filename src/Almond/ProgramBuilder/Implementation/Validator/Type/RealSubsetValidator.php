<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RealSubsetTypeNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class RealSubsetValidator implements PreBuildValidator {

	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			RealSubsetTypeNode::class
		) as $realSubsetTypeNode) {
			$existing = [];
			foreach ($realSubsetTypeNode->values as $subsetValue) {
				$n = (string)$subsetValue->value;
				$existing[$n] ??= [];
				$existing[$n][] = $subsetValue;
			}
			foreach ($existing as $n => $nodes) {
				if (count($nodes) > 1) {
					$result = $result->withAddedError(
						PreBuildValidationErrorType::nonUniqueValueDefinition,
						"Real subset value '$n' is listed " . count($nodes) . " times in subset type",
						$nodes
					);
				}
			}
		}

		return $result;
	}

}