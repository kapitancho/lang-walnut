<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;

interface FunctionValueFactory {
	public function function(
		NameAndType  $parameter,
		NameAndType  $dependency,
		Type         $returnType,
		FunctionBody $functionBody
	): FunctionValue;
}