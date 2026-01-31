<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface UserlandMethodFactory {
	public function method(
		TypeName     $targetType,
		MethodName   $methodName,
		NameAndType  $parameter,
		NameAndType  $dependency,
		Type         $returnType,
		FunctionBody $functionBody
	): UserlandMethod;
}