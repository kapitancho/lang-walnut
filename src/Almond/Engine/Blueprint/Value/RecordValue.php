<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownProperty;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RecordType;

interface RecordValue extends Value {
	/** @var array<string, Value> */
	public array $values { get; }
	public RecordType $type { get; }

	public function valueOf(string $key): Value|UnknownProperty;
}