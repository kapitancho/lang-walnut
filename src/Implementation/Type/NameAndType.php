<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\NameAndType as NameAndTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class NameAndType implements NameAndTypeInterface, JsonSerializable {
	public function __construct(
		public Type $type,
		public VariableNameIdentifier|null $name
	) {}

	public function __toString(): string {
		return $this->name === null ?
			$this->type :
			sprintf("%s: %s", $this->name, $this->type);
	}

	public function jsonSerialize(): array {
		return ['type' => $this->type, 'name' => $this->name];
	}

}