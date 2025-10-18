<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

interface EscapeCharHandler {
	public function escape(string $value): string;
	public function unescape(string $value): string;
}