<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NamedTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ProxyTypeNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationRequest;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationSuccess;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidator;

final readonly class TypeNameExistsValidator implements PreBuildValidator {
	private const array buildInTypes = [
		'Any', 'Nothing', 'Boolean', 'True', 'False', 'Integer', 'Real', 'String', 'Type', 'Array', 'Map', 'Set',
		'Shape', 'Impure', 'Error', 'Mutable', 'Type', 'Atom', 'Data', 'Open', 'Sealed', 'Named', 'Enumeration'
	];

	public function validate(PreBuildValidationRequest $request): PreBuildValidationSuccess|PreBuildValidationFailure {
		$result = $request->result;

		// First pass: collect all type definitions (for ProxyTypeNode checks)
		$allDefinedTypes = [];
		$definedSoFar = [];
		$forwardReferences = [];
		// Second pass: iterate in order to check forward references
		$undefinedLazyTypes = [];

		foreach(self::buildInTypes as $typeName) {
			$allDefinedTypes[$typeName] = true;
			$definedSoFar[$typeName] = true;
		}
		foreach ($request->nodeIteratorFactory->filterByType(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			AddTypeNode::class
		) as $addTypeNode) {
			$allDefinedTypes[$addTypeNode->name->name] = true;
		}


		foreach ($request->nodeIteratorFactory->filterByTypes(
			$request->nodeIteratorFactory->recursive($request->rootNode),
			[AddTypeNode::class, NamedTypeNode::class, ProxyTypeNode::class]
		) as $node) {
			if ($node instanceof AddTypeNode) {
				// Type definition - add to "defined so far"
				$definedSoFar[$node->name->name] = true;
			} elseif ($node instanceof NamedTypeNode) {
				// Regular type reference - must be defined before use
				$typeName = $node->name->name;
				if (!isset($definedSoFar[$typeName])) {
					$forwardReferences[$typeName] ??= [];
					$forwardReferences[$typeName][] = $node;
				}
			} elseif ($node instanceof ProxyTypeNode) {
				// Lazy type reference - just needs to exist anywhere
				$typeName = $node->name->name;
				if (!isset($allDefinedTypes[$typeName])) {
					$undefinedLazyTypes[$typeName] ??= [];
					$undefinedLazyTypes[$typeName][] = $node;
				}
			}
		}

		// Report forward reference errors (type used before defined)
		foreach ($forwardReferences as $typeName => $nodes) {
			// Only report if the type IS defined somewhere (just not before this reference)
			// If it's not defined at all, it's a different error
			if (isset($allDefinedTypes[$typeName])) {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::missingType,
					"Type '$typeName' is used before it is defined (forward reference not allowed without backslash)",
					$nodes
				);
			} else {
				$result = $result->withAddedError(
					PreBuildValidationErrorType::missingType,
					"Type '$typeName' is not defined (referenced " . count($nodes) . " time" . (count($nodes) > 1 ? 's' : '') . ")",
					$nodes
				);
			}
		}

		// Report undefined lazy type errors
		foreach ($undefinedLazyTypes as $typeName => $nodes) {
			$result = $result->withAddedError(
				PreBuildValidationErrorType::missingType,
				"Type '$typeName' is not defined (lazy reference, " . count($nodes) . " time" . (count($nodes) > 1 ? 's' : '') . ")",
				$nodes
			);
		}

		return $result;
	}

}
