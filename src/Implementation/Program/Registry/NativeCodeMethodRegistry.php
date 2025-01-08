<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\MethodDraft;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\Registry\MethodDraftRegistry;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class NativeCodeMethodRegistry implements MethodRegistry, MethodDraftRegistry {
	private MethodRegistry $methodRegistry;
	public function __construct(
		private MethodExecutionContext $context,
		private NativeCodeTypeMapper   $typeMapper,
		MethodRegistry|null            $methodRegistry,
		private DependencyContainer    $dependencyContainer,
		private array                  $lookupNamespaces
	) {
		$this->methodRegistry = $methodRegistry ?? $this;
	}

	public function method(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		$candidates = $this->typeMapper->getTypesFor($targetType);
		$method = ucfirst($methodName->identifier);

		foreach($candidates as $candidate) {
			foreach($this->lookupNamespaces as $namespace) {
				$className = $namespace . '\\' . $candidate . '\\' . $method;
				if (class_exists($className)) {
					return new $className($this->context, $this->methodRegistry, $this->typeMapper, $this->dependencyContainer);
				}
			}
		}
		return UnknownMethod::value;
	}

	public function methodDraft(Type $targetType, MethodNameIdentifier $methodName): MethodDraft|UnknownMethod {
		return $this->method($targetType, $methodName);
	}
}