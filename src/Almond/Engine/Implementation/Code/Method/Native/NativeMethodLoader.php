<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NamespaceConfigMap as NamespaceConfigMapInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NativeMethodLoader as NativeMethodLoaderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;

final readonly class NativeMethodLoader implements NativeMethodLoaderInterface {

	public function __construct(
		private ProgramContext $programContext,

		private NamespaceConfigMapInterface $namespaceConfigMap
	) {}

	private function getClassName(TypeName $typeName, MethodName $methodName): string {
		return $this->namespaceConfigMap->getNamespaceFor($typeName)
			. '\\' . ucfirst($methodName->identifier);
	}

	private function loadClassByName(string $className): NativeMethod|UnknownMethod {
		if (class_exists($className) && is_subclass_of($className, NativeMethod::class)) {
			return new $className(
				$this->programContext->validationFactory,
				$this->programContext->typeRegistry,
				$this->programContext->valueRegistry,
				$this->programContext->methodContext,
				$this->programContext->variableScopeFactory,
			);
		}
		return UnknownMethod::value;
	}

	public function loadNativeMethod(TypeName $typeName, MethodName $methodName): NativeMethod|UnknownMethod {
		return $this->getClassName($typeName, $methodName)
			|> $this->loadClassByName(...);
	}

	/** @return list<NativeMethod> */
	public function loadNativeMethods(array $typeNames, MethodName $methodName): array {
		$methods = [];
		foreach ($typeNames as $typeName) {
			$method = $this->loadNativeMethod($typeName, $methodName);
			if ($method instanceof NativeMethod) {
				$methods[] = $method;
			}
		}
		return $methods;
	}

}