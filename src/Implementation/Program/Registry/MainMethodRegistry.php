<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Implementation\Function\UnionMethodCall;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MainMethodRegistry implements MethodRegistry {
	use BaseType;

	private NestedMethodRegistry $registry;

	public function __construct(
		private MethodExecutionContext $nativeCodeContext,
		NativeCodeTypeMapper           $nativeCodeTypeMapper,
		CustomMethodRegistryBuilder    $customMethodRegistryBuilder,
		DependencyContainer            $dependencyContainer,
		private array                  $lookupNamespaces
	) {
		$this->registry = new NestedMethodRegistry(
			$customMethodRegistryBuilder,
			new NativeCodeMethodRegistry(
				$nativeCodeContext,
				$nativeCodeTypeMapper,
				$this,
				$dependencyContainer,
				$this->lookupNamespaces
			)
		);
	}

	public function method(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		$baseType = $this->toBaseType($targetType);
		if ($baseType instanceof IntersectionType) {
			$methods = [];
			foreach($baseType->types() as $type) {
				$method = $this->method($type, $methodName);
				if ($method instanceof Method) {
					$methods[] = [$type, $method];
				}
			}
			if (count($methods) > 0) {
				$unique = [];
				foreach($methods as $method) {
					$unique[$method[0]::class . $method[1]::class] = $method;
				}
				if (count($unique) === 1) {
					return $unique[array_key_first($unique)][1];//$methods[0][1];
				}
				$method = $this->registry->method($targetType, $methodName);
				return $method instanceof Method ? $method : throw new AnalyserException(
					sprintf(
						"Cannot call method '%s' on type '%s': ambiguous method",
						$methodName,
						$targetType
					)
				);
			}
		}
		if ($baseType instanceof UnionType) {
			$methods = [];
			foreach($baseType->types() as $type) {
				$method = $this->method($type, $methodName);
				if ($method instanceof Method) {
					$methods[] = [$type, $method];
				} else {
					$methods = [];
					break;
				}
			}
			if (count($methods) > 0) {
				return new UnionMethodCall($this->nativeCodeContext, $methods);
			}
		}
		return $this->registry->method($targetType, $methodName);
	}
}