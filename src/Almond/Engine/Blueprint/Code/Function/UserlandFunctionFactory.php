<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

interface UserlandFunctionFactory {
	public function create(
		NameAndType $target,
		NameAndType $parameter,
		NameAndType $dependency,
		Type $returnType,
		FunctionBody $functionBody
	): UserlandFunction;
}