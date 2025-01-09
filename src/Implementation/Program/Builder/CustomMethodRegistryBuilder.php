<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodDraftRegistry;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry as CustomMethodRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Implementation\Function\CustomMethod;
use Walnut\Lang\Implementation\Function\CustomMethodDraft;

final readonly class CustomMethodRegistryBuilder implements CustomMethodRegistryBuilderInterface {

	public function __construct() {}

	public function buildFromDrafts(CustomMethodDraftRegistry $customMethodDraftRegistry): MethodRegistry&CustomMethodRegistryInterface {
		$customMethodDrafts = $customMethodDraftRegistry->customMethods;

		$customMethods = array_map(
			fn(array $methods): array => array_map(
				$this->convertToCustomMethod(...),
				$methods
			),
			$customMethodDrafts
		);

		return new CustomMethodRegistry($customMethods);
	}

	private function convertToCustomMethod(CustomMethodDraft $customMethodDraft): CustomMethod {
		return new CustomMethod(
			$customMethodDraft->targetType,
			$customMethodDraft->methodName,
			$customMethodDraft->parameterType,
			$customMethodDraft->dependencyType,
			$customMethodDraft->returnType,
			$customMethodDraft->functionBody->functionBody
		);
	}

}