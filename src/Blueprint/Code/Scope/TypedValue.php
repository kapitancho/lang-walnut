<?php

namespace Walnut\Lang\Blueprint\Code\Scope;

use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final class TypedValue {

	/** @param list<Type> $types */
	private function __construct(
		public readonly array $types,
		public readonly Value $value,
	) {}

	public Type $type {
		get => $this->types[array_key_first($this->types)];
	}

	public function withType(Type $type): self {
		return new self([(string)$type => $type] + $this->types, $this->value);
	}

	public function withAdditionalType(Type $type): self {
		return new self($this->types + [(string)$type => $type], $this->value);
	}

	public function isSubtypeOf(Type $type): bool {
		return array_any(
			$this->types,
			fn(Type $t): bool => $t->isSubtypeOf($type)
		);
	}

	public static function forValue(Value $value): self {
		return new self([$value->type], $value);
	}
}