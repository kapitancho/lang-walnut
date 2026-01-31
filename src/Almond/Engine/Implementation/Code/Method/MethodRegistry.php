<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodRegistry as MethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NativeMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeFinder;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;

final readonly class MethodRegistry implements MethodRegistryInterface {
	public function __construct(
		private TypeFinder             $typeFinder,
		private NativeMethodRegistry   $nativeMethodRegistry,
		private UserlandMethodRegistry $userlandMethodRegistry
	) {}

	public function methodFor(Type $type, MethodName $methodName): Method|UnknownMethod {
		if ($methodName->identifier === 'as') {
			$methodName = new MethodName('castAs');
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