<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

interface ResultType extends Type {
	public Type $returnType { get; }
	public Type $errorType { get; }
}