<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddTypeNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class DuplicateTypeNameValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		$addedTypes = [];
		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			AddTypeNode::class
		) as $addTypeNode) {
			$addedTypes[$addTypeNode->name->name] ??= [];
			$addedTypes[$addTypeNode->name->name][] = $addTypeNode;
		}

		foreach ($addedTypes as $typeName => $typeNodes) {
			if (count($typeNodes) > 1) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::nonUniqueValueDefinition,
					"Type '$typeName' is defined " . count($typeNodes) . " times",
					$typeNodes
				);
			}
		}

		return $result;
	}
}
