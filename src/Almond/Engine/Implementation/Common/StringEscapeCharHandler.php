<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Common;

use Walnut\Lang\Almond\Engine\Blueprint\Common\EscapeCharHandler;

final readonly class StringEscapeCharHandler implements EscapeCharHandler {

	private const array escapeOriginalChars = ['\\', "\n", "\t", "'"];
	private const array escapedChars = ['\\\\', '\n', '\t', '\`'];

	public function escape(string $value): string {
		return
			"'" .
			str_replace(self::escapeOriginalChars, self::escapedChars, $value) .
			"'";
	}
}