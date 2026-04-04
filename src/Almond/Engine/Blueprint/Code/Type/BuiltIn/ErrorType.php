<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

interface ErrorType extends ResultType {
	public NothingType $returnType { get; }
}