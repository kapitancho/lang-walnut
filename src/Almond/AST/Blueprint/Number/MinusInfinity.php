<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Number;

use JsonSerializable;

enum MinusInfinity implements JsonSerializable {
	case value;

	public function jsonSerialize(): string {
		return 'MinusInfinity';
	}
}