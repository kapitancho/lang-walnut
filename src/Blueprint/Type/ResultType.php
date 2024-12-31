<?php

namespace Walnut\Lang\Blueprint\Type;

interface ResultType extends Type {
	public Type $returnType { get; }
	public Type $errorType { get; }
}