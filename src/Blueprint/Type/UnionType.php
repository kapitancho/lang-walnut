<?php

namespace Walnut\Lang\Blueprint\Type;

interface UnionType extends Type {
	/** @param non-empty-list<Type> $types */
	public array $types { get; }
}