<?php

namespace Walnut\Lang\Blueprint\Type;

interface TupleType extends Type {
    /**
     * @return non-empty-list<Type>
     */
    public function types(): array;

	public function restType(): Type;

	public function asArrayType(): ArrayType;

	/** @throws UnknownProperty */
	public function typeOf(int $index): Type;
}