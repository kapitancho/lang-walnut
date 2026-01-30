<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface NativeMethodRegistry {
	/** @return list<NativeMethod> */
	public function nativeMethods(Type $type, MethodName $methodName): array;
}