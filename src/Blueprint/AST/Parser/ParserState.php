<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

interface ParserState {
	public int $i { get; }
	public array $result { get; }
}