<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodDraft;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodDraftRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class NestedMethodDraftRegistry implements MethodDraftRegistry {
	/** @var MethodDraftRegistry[] $registries */
	private array $registries;

	public function __construct(
		MethodDraftRegistry ... $registries,
	) {
		$this->registries = $registries;
	}

	public function methodDraft(Type $targetType, MethodNameIdentifier $methodName): MethodDraft|UnknownMethod {
		foreach ($this->registries as $registry) {
			$method = $registry->method($targetType, $methodName);
			if ($method instanceof MethodDraft) {
				return $method;
			}
		}
		return UnknownMethod::value;
	}
}