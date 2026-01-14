<?php

namespace Walnut\Lang\Blueprint\Type;

interface TupleType extends CompositeType {
	/**
      * @param non-empty-list<Type> $types
      */
	public array $types { get; }
	public Type $restType { get; }

	public function asArrayType(): ArrayType;

	/** @throws UnknownProperty */
	public function typeOf(int $index): Type;
}