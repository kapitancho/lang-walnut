<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodRegistry as MethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NativeMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class MethodRegistry implements MethodRegistryInterface {
	use BaseType;

	public function __construct(
		private ProgramContext         $programContext,
		private NativeMethodRegistry   $nativeMethodRegistry,
	) {}

	public function methodFor(Type $type, MethodName $methodName): Method|UnknownMethod {
		if ($methodName->identifier === 'as') {
			$methodName = new MethodName('castAs');
		}
		$baseType = $this->toBaseType($type);
		if ($baseType instanceof IntersectionType) {
			foreach($baseType->types as $baseType) {
				$method = $this->methodFor($baseType, $methodName);
				if (!$method instanceof UnknownMethod) {
					return $method;
				}
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
				return new UnionMethodCall(
					$this->programContext->validationFactory,
					$this->programContext->typeRegistry,
					$methods
				);
			}
		}

		$userlandMethods = $this->programContext->userlandMethodRegistry->methodsByName($methodName);
		foreach(array_reverse($userlandMethods) as $userlandMethod) {
			$typeByName = $this->programContext->typeRegistry->typeByName($userlandMethod->targetType);
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