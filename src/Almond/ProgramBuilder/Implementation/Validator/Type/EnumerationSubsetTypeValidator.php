<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class EnumerationSubsetTypeValidator implements PreBuildValidator {
	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		$enums = [];
		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			AddEnumerationTypeNode::class
		) as $addEnumerationTypeNode) {
			$enumValues = array_map(
				fn(EnumerationValueNameNode $ev): string => $ev->name,
				$addEnumerationTypeNode->values
			);
			$enums[$addEnumerationTypeNode->name->name] = $enumValues;
		}

		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			EnumerationSubsetTypeNode::class
		) as $enumSubsetTypeNode) {
			$enumName = $enumSubsetTypeNode->name->name;
			$enumValues = $enums[$enumName] ?? null;

			if ($enumValues === null) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::missingType,
					"Enumeration '$enumName' not found for subset type",
					[$enumSubsetTypeNode]
				);
				continue;
			}

			$existing = [];
			foreach ($enumSubsetTypeNode->values as $enumValue) {
				$n = $enumValue->name;
				if (!in_array($n, $enumValues, true)) {
					$result = $result->withAddedError(
						PreBuildValidationErrorType::missingValue,
						"Enumeration value '$n' not found in enumeration '$enumName'",
						[$enumValue]
					);
				}
				$existing[$n] ??= [];
				$existing[$n][] = $enumValue;
			}
			foreach ($existing as $n => $nodes) {
				if (count($nodes) > 1) {
					$result = $result->withAddedError(
						PreBuildValidationErrorType::nonUniqueValueDefinition,
						"Enumeration value '$n' is listed " . count($nodes) . " times in subset type",
						$nodes
					);
				}
			}
		}

		return $result;
	}
}
