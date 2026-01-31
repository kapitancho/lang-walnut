<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Range;

use JsonSerializable;

enum PlusInfinity implements JsonSerializable {
	case value;

	public function jsonSerialize(): string {
		return 'PlusInfinity';
	}
}