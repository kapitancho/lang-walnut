<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface ResultType extends Type {
	public Type $returnType { get; }
	public Type $errorType { get; }
}