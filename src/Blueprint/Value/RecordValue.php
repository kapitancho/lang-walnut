<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\UnknownProperty;

interface RecordValue extends Value {
	public array $values { get; }
	public RecordType $type { get; }

	/** @throws UnknownProperty */
	public function valueOf(string $propertyName): Value;
}