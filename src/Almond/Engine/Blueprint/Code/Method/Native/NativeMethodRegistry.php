<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;

interface NativeMethodRegistry {
	/** @return list<NativeMethod> */
	public function nativeMethods(Type $type, MethodName $methodName): array;
}