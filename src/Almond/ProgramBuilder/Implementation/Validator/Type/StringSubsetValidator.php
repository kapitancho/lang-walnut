<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\StringSubsetTypeNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class StringSubsetValidator implements PreBuildValidator {

	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			StringSubsetTypeNode::class
		) as $stringSubsetTypeNode) {
			$existing = [];
			foreach ($stringSubsetTypeNode->values as $subsetValue) {
				$n = $subsetValue->value;
				$existing[$n] ??= [];
				$existing[$n][] = $subsetValue;
			}
			foreach ($existing as $n => $nodes) {
				if (count($nodes) > 1) {
					$result = $result->withAddedError(
						PreBuildValidationErrorType::nonUniqueValueDefinition,
						"String subset value '$n' is listed " . count($nodes) . " times in subset type",
						$nodes
					);
				}
			}
		}

		return $result;
	}

}