<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserState;
use Walnut\Lib\Walex\Token;

interface SourceLocator {
	public string $moduleName { get; }
	/** @var list<Token> */
	public array $tokens { get; }
	public ParserState $state { get; }

	public function getSourceLocation(): SourceLocation;
}