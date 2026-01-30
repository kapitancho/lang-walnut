<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface IntersectionType extends Type {
	/** @var non-empty-list<Type> */
	public array $types { get; }
}