<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Abc\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

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