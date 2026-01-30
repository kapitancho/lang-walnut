<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\AtomValueNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class AtomValueTypeExistsValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		$atomTypes = [];
		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			AddAtomTypeNode::class
		) as $addAtomTypeNode) {
			$atomTypes[$addAtomTypeNode->name->name] = $addAtomTypeNode;
		}

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			AtomValueNode::class
		) as $atomValueNode) {
			$atomTypeName = $atomValueNode->name->name;
			if (!isset($atomTypes[$atomTypeName])) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::missingType,
					"Atom type '$atomTypeName' not found for atom value",
					[$atomValueNode]
				);
			}
		}

		return $result;
	}
}
