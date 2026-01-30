<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Number;

use JsonSerializable;

enum PlusInfinity implements JsonSerializable {
	case value;

	public function jsonSerialize(): string {
		return 'PlusInfinity';
	}
}