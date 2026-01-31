<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;

interface TupleType extends Type {
	/** @param list<Type> $types */
	public array $types { get; }
	public Type $restType { get; }

	public function asArrayType(): ArrayType;

	public function typeOf(int $index): Type|UnknownProperty;
}