<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\BytesPad;

final readonly class PadRight extends BytesPad {

	protected function getPadType(): int {
		return STR_PAD_RIGHT;
	}

}