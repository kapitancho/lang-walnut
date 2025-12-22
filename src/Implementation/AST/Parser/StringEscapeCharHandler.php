<?php

namespace Walnut\Lang\Implementation\AST\Parser;

use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler as EscapeCharHandlerInterface;

final readonly class StringEscapeCharHandler implements EscapeCharHandlerInterface {

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