<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\DataExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddDataTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\DataValueNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class DataTypeExistsValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		$dataTypes = [];
		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			AddDataTypeNode::class
		) as $addDataTypeNode) {
			$dataTypes[$addDataTypeNode->name->name] = $addDataTypeNode;
		}

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			DataValueNode::class
		) as $dataValueNode) {
			$dataTypeName = $dataValueNode->name->name;
			if (!isset($dataTypes[$dataTypeName])) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::missingType,
					"Data type '$dataTypeName' not found for data value",
					[$dataValueNode]
				);
			}
		}

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			DataExpressionNode::class
		) as $dataExpressionNode) {
			$dataTypeName = $dataExpressionNode->typeName->name;
			if (!isset($dataTypes[$dataTypeName])) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::missingType,
					"Data type '$dataTypeName' not found for data expression",
					[$dataExpressionNode]
				);
			}
		}

		return $result;
	}
}
