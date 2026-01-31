<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

interface FunctionType extends Type {
	public Type $parameterType { get; }
	public Type $returnType { get; }
}