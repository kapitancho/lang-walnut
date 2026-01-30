<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

interface EscapeCharHandler {
	public function escape(string $value): string;
	public function unescape(string $value): string;
}