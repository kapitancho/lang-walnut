<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Common;

use Walnut\Lang\Almond\Engine\Blueprint\Common\EscapeCharHandler;

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

}