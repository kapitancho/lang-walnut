<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UnknownMethod;

interface NativeMethodLoader {
	public function loadNativeMethod(TypeName $typeName, MethodName $methodName): NativeMethod|UnknownMethod;
	/** @return list<NativeMethod> */
	public function loadNativeMethods(array $typeNames, MethodName $methodName): array;
}