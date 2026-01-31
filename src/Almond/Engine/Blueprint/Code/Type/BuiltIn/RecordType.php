<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;

interface RecordType extends Type {
	/**
      * @param array<string, Type> $types
      */
	public array $types { get; }
	public Type $restType { get; }

	public function asMapType(): MapType;

	public function typeOf(string $key): Type|UnknownProperty;
}