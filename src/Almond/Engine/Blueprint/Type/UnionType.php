<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface UnionType extends Type {
	/** @var non-empty-list<Type> */
	public array $types { get; }
}