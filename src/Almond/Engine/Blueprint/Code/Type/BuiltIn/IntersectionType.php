<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

interface IntersectionType extends Type {
	/** @var non-empty-list<Type> */
	public array $types { get; }
}