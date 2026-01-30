<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserState;

interface SourceLocator {
	public string $moduleName { get; }
	public array $tokens { get; }
	public ParserState $state { get; }

	public function getSourceLocation(): SourceLocation;
}