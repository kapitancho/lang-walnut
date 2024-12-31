<?php

namespace Walnut\Lang\Blueprint\Type;

interface IntersectionType extends Type {
	/** @param non-empty-list<Type> $types */
	public array $types { get; }
}