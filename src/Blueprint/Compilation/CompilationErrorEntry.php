<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\Node;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lib\Walex\SourcePosition;
use Walnut\Lib\Walex\Token as LexerToken;

interface CompilationErrorEntry {
	public string $errorMessage { get; }
	public string|null $moduleName { get; }
	public LexerToken|null $token { get; }
	public Node|null $node { get; }
	public null|string $entryDescription { get; }
	/** @var SourceLocation|SourcePosition|list<string>|null */
	public SourceLocation|SourcePosition|array|null $location { get; }
}