<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface NativeMethodRegistry {
	/** @return list<NativeMethod> */
	public function nativeMethods(Type $type, MethodName $methodName): array;
	public function nativeMethodForTypeName(TypeName $typeName, MethodName $methodName): NativeMethod|UnknownMethod;
}