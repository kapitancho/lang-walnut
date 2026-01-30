<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType as NameAndTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

final readonly class NameAndType implements NameAndTypeInterface {
	public function __construct(public Type $type, public VariableName|null $name) {}

	public function __toString(): string {
		return $this->name === null ?
			$this->type :
			sprintf("%s: %s", $this->name, $this->type);
	}

	public function jsonSerialize(): array {
		return ['type' => $this->type, 'name' => $this->name];
	}

}