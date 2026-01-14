<?php

namespace Walnut\Lang\Blueprint\Type;

interface UnionType extends CompositeType {
	/** @param non-empty-list<Type> $types */
	public array $types { get; }
}