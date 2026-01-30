<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface UserlandMethodBuilder {
	public function addMethod(
		TypeName     $targetType,
		MethodName   $methodName,
		NameAndType  $parameter,
		NameAndType  $dependency,
		Type         $returnType,
		FunctionBody $functionBody,
	): UserlandMethod;
}