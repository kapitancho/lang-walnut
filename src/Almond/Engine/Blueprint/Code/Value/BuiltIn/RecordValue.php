<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface RecordValue extends Value {
	/** @var array<string, Value> */
	public array $values { get; }
	public RecordType $type { get; }

	public function valueOf(string $key): Value|UnknownProperty;
}