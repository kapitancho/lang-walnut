<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownProperty;

interface RecordType extends Type {
	/**
      * @param array<string, Type> $types
      */
	public array $types { get; }
	public Type $restType { get; }

	public function asMapType(): MapType;

	public function typeOf(string $key): Type|UnknownProperty;
}