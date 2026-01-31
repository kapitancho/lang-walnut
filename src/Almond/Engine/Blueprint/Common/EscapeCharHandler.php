<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common;

interface EscapeCharHandler {
	public function escape(string $value): string;
}