<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodRegistry as MethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NativeMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeFinder;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class MethodRegistry implements MethodRegistryInterface {
	use BaseType;

	public function __construct(
		private TypeFinder             $typeFinder,
		private NativeMethodRegistry   $nativeMethodRegistry,
		private UserlandMethodRegistry $userlandMethodRegistry
	) {}

	public function methodFor(Type $type, MethodName $methodName): Method|UnknownMethod {
		if ($methodName->identifier === 'as') {
			$methodName = new MethodName('castAs');
		}
		$baseType = $this->toBaseType($type);
		if ($baseType instanceof IntersectionType) {
			$methods = [];
			foreach($baseType->types as $baseType) {
				$method = $this->methodFor($baseType, $methodName);
				if ($method instanceof Method) {
					$methods[] = [$baseType, $method];
				}
			}
			if (count($methods) > 0) {
				$unique = [];
				foreach($methods as $method) {
					$uKey = $method[1] instanceof NativeMethod ? $method[1]::class : (string)$method[0];
					$unique[$uKey] = $method;
				}
				if (count($unique) === 1) {
					return $unique[array_key_first($unique)][1];//$methods[0][1];
				}
				$method = $this->methodFor($type, $methodName);

				//TODO
				return $method instanceof Method ? $method :
					// @codeCoverageIgnoreStart
					throw new AnalyserException(
						sprintf(
							"Cannot call method '%s' on type '%s': ambiguous method",
							$methodName,
							$type
						)
					// @codeCoverageIgnoreEnd
					);
			}
		}
		if ($baseType instanceof UnionType) {
			$methods = [];
			foreach($baseType->types as $baseType) {
				$method = $this->methodFor($baseType, $methodName);
				if ($method instanceof Method) {
					$methods[] = [$baseType, $method];
				} else {
					$methods = [];
					break;
				}
			}
			if (count($methods) > 0) {
				//TODO
				return new UnionMethodCall(null, null, $methods);
			}
		}

		$userlandMethods = $this->userlandMethodRegistry->methodsByName($methodName);
		foreach(array_reverse($userlandMethods) as $userlandMethod) {
			$typeByName = $this->typeFinder->typeByName($userlandMethod->targetType);
			if ($type->isSubtypeOf($typeByName)) {
				return $userlandMethod;
			}
		}
		$nativeMethods = $this->nativeMethodRegistry->nativeMethods($type, $methodName);
		if (count($nativeMethods) > 0) {
			return array_first($nativeMethods);
		}
		return UnknownMethod::value;
	}
}