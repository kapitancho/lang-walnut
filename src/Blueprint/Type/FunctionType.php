<?php

namespace Walnut\Lang\Blueprint\Type;

interface FunctionType extends Type {
	public Type $parameterType { get; }
	public Type $returnType { get; }
}