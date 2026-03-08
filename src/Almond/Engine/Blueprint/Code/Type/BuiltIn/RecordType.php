<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType as MapTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;

interface RecordType extends MapType {
	/**
      * @var array<string, Type> $types
      */
	public array $types { get; }
	public Type $restType { get; }

	public MapTypeInterface $mapType { get; }

	public function typeOf(string $key): Type|UnknownProperty;
}