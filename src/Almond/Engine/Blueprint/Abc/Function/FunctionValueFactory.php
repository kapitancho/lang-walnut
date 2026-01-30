<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Abc\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\FunctionValue;

interface FunctionValueFactory {
	public function function(
		NameAndType  $parameter,
		NameAndType  $dependency,
		Type         $returnType,
		FunctionBody $functionBody
	): FunctionValue;
}