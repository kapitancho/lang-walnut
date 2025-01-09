<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class NativeCodeMethodRegistry implements MethodRegistry {
	public function __construct(
		private NativeCodeTypeMapper   $typeMapper,
		private array                  $lookupNamespaces
	) {}

	public function method(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		$candidates = $this->typeMapper->getTypesFor($targetType);
		$method = ucfirst($methodName->identifier);

		foreach($candidates as $candidate) {
			foreach($this->lookupNamespaces as $namespace) {
				$className = $namespace . '\\' . $candidate . '\\' . $method;
				if (class_exists($className)) {
					return new $className($this->typeMapper);
				}
			}
		}
		return UnknownMethod::value;
	}
}