<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Function;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;

final readonly class NameAndType implements Stringable {
	public function __construct(public Type $type, public VariableName|null $name) {
	}

	public function __toString(): string {
		return $this->name === null ?
			$this->type :
			sprintf("%s: %s", $this->name, $this->type);
	}

	public function jsonSerialize(): array {
		return ['type' => $this->type, 'name' => $this->name];
	}

}