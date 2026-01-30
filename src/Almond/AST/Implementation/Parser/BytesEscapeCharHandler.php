<?php

namespace Walnut\Lang\Almond\AST\Implementation\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Parser\EscapeCharHandler;

final readonly class BytesEscapeCharHandler implements EscapeCharHandler {

	public function escape(string $value): string {
		$result = '"';
		$len = strlen($value);

		for ($i = 0; $i < $len; $i++) {
			$byte = $value[$i];
			$ord = ord($byte);

			$result .= match($byte) {
				'\\' => '\\\\',
				"\n" => '\n',
				"\t" => '\t',
				'"' => '\``',
				default => ($ord >= 32 && $ord <= 126)
					? $byte
					: '\\' . strtoupper(str_pad(dechex($ord), 2, '0', STR_PAD_LEFT))
			};
		}

		return $result . '"';
	}

	public function unescape(string $value): string {
		$withoutQuotes = substr($value, 1, -1);

		return preg_replace_callback(
			'/\\\\(\\\\|n|t|``|[0-9A-F]{2})/',
			static fn(array $matches): string => match($matches[1]) {
				'\\' => '\\',
				'n' => "\n",
				't' => "\t",
				'``' => '"',
				default => chr(hexdec($matches[1]))
			},
			$withoutQuotes
		) ?? $withoutQuotes;
	}
}