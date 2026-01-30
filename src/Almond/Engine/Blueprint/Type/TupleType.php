<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownProperty;

interface TupleType extends Type {
	/** @param list<Type> $types */
	public array $types { get; }
	public Type $restType { get; }

	public function asArrayType(): ArrayType;

	public function typeOf(int $index): Type|UnknownProperty;
}