<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface NativeMethodLoader {
	public function loadNativeMethod(TypeName $typeName, MethodName $methodName): NativeMethod|UnknownMethod;
	/** @return list<NativeMethod> */
	public function loadNativeMethods(array $typeNames, MethodName $methodName): array;
}