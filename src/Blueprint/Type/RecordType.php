<?php

namespace Walnut\Lang\Blueprint\Type;

interface RecordType extends Type {
	/**
      * @param non-empty-list<Type> $types
      */
	public array $types { get; }
	public Type $restType { get; }

	public function asMapType(): MapType;

	/** @throws UnknownProperty */
	public function typeOf(string $propertyName): Type;
}