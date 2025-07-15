<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

use Walnut\Lib\Walex\Token;

interface EscapeCharHandler {
	public function escape(string $value): string;
	public function unescape(string $value): string;
}