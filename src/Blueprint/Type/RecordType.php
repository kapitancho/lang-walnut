<?php

namespace Walnut\Lang\Blueprint\Type;

interface RecordType extends Type {
    /**
     * @return non-empty-list<Type>
     */
    public function types(): array;

	public function restType(): Type;

	public function asMapType(): MapType;

	/** @throws UnknownProperty */
	public function typeOf(string $propertyName): Type;
}