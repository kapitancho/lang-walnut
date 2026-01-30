<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface FunctionType extends Type {
	public Type $parameterType { get; }
	public Type $returnType { get; }
}