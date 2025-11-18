<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

interface ParserState {
	public int $i { get; set; }
	public array $result { get; set; }
}