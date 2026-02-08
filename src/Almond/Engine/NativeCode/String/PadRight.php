<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\StringPad;

final readonly class PadRight extends StringPad {

	protected function getPadType(): int {
		return STR_PAD_RIGHT;
	}

}
