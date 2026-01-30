<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Abc\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface UserlandFunctionFactory {
	public function create(
		NameAndType $target,
		NameAndType $parameter,
		NameAndType $dependency,
		Type $returnType,
		FunctionBody $functionBody
	): UserlandFunction;
}