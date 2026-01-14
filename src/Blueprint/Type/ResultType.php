<?php

namespace Walnut\Lang\Blueprint\Type;

interface ResultType extends CompositeType {
	public Type $returnType { get; }
	public Type $errorType { get; }
}