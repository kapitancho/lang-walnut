<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

interface IntegerSubsetType extends IntegerType {
	/** @var list<number> */
	public array $subsetValues { get; }
}
