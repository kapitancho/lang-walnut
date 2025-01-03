<?php

namespace Walnut\Lang\Blueprint\Range;

use JsonSerializable;

enum PlusInfinity implements JsonSerializable {
	case value;

	public function jsonSerialize(): string {
		return 'PlusInfinity';
	}
}