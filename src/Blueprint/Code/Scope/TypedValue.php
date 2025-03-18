<?php

namespace Walnut\Lang\Blueprint\Code\Scope;

use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final class TypedValue {

	private function __construct(
		public readonly Value $value,
	) {}

	public Type $type {
		get => $this->value->type;
	}

	public function isSubtypeOf(Type $type): bool {
		return $this->type->isSubtypeOf($type);
	}

	public static function forValue(Value $value): self {
		return new self($value);
	}
}