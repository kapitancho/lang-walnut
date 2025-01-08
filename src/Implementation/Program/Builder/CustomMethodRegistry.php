<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod as CustomMethodInterface;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class CustomMethodRegistry implements MethodRegistry, JsonSerializable {

	/** @param array<string, list<CustomMethodInterface>> $methods */
	public function __construct(
		private array $methods,
	) {}

	public function method(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		foreach(array_reverse($this->methods[$methodName->identifier] ?? []) as $method) {
			if ($targetType->isSubtypeOf($method->targetType)) {
				return $method;
			}
		}
		return UnknownMethod::value;
	}

	public function jsonSerialize(): array {
		return $this->methods;
	}
}