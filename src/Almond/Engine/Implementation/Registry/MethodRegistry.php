<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\MethodRegistry as MethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Native\NativeMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeFinder;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnknownType;

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
			if ($typeByName !== UnknownType::value && $type->isSubtypeOf($typeByName)) {
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