<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\FunctionCompositionMode;

interface FunctionType extends CompositeType {
	public Type $parameterType { get; }
	public Type $returnType { get; }

	public function composeWith(FunctionType $nextFunctionType, FunctionCompositionMode $compositionMode): FunctionType;
}