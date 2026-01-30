<?php

namespace Walnut\Lang\Almond\AST\Implementation\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Parser\EscapeCharHandler;

final readonly class StringEscapeCharHandler implements EscapeCharHandler {

	private const array escapeOriginalChars = ['\\', "\n", "\t", "'"];
	private const array escapedChars = ['\\\\', '\n', '\t', '\`'];

	private const array unescapeOriginalChars = ['\`', '\n', '\t', '\\\\'];
	private const array unescapedChars = ["'", "\n", "\t", "\\"];

	public function escape(string $value): string {
		return
			"'" .
			str_replace(self::escapeOriginalChars, self::escapedChars, $value) .
			"'";
	}

	public function unescape(string $value): string {
		return str_replace(
			self::unescapeOriginalChars,
			self::unescapedChars,
			substr($value, 1, -1)
		);
	}
}