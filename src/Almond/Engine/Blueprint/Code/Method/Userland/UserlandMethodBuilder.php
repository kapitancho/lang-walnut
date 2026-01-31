<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

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