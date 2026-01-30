<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Range;

use JsonSerializable;

enum MinusInfinity implements JsonSerializable {
	case value;

	public function jsonSerialize(): string {
		return 'MinusInfinity';
	}
}