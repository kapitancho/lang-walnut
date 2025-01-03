<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use JsonSerializable;

enum MinusInfinity implements JsonSerializable {
	case value;

	public function jsonSerialize(): string {
		return 'MinusInfinity';
	}
}