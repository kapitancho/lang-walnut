<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class EnumerationTypeValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			AddEnumerationTypeNode::class
		) as $addEnumerationTypeNode) {
			$existing = [];
			foreach ($addEnumerationTypeNode->values as $enumValue) {
				$n = $enumValue->name;
				$existing[$n] ??= [];
				$existing[$n][] = $enumValue;
			}
			foreach ($existing as $n => $nodes) {
				if (count($nodes) > 1) {
					$result = $result->withAddedError(
						PreBuildValidationErrorType::nonUniqueValueDefinition,
						"Enumeration value '$n' is listed " . count($nodes) . " times in enumeration '{$addEnumerationTypeNode->name->name}'",
						$nodes
					);
				}
			}
		}

		return $result;
	}
}
