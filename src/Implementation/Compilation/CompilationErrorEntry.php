<?php

namespace Walnut\Lang\Implementation\Compilation;


use Walnut\Lang\Blueprint\AST\Node\Node;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Compilation\CompilationErrorEntry as CompilationErrorEntryInterface;
use Walnut\Lib\Walex\SourcePosition;
use Walnut\Lib\Walex\Token;

final readonly class CompilationErrorEntry implements CompilationErrorEntryInterface {

	/** @param SourceLocation|SourcePosition|list<string>|null $location */
	public function __construct(
		public string $errorMessage,
		public null|string $moduleName,
		public null|Token $token,
		public null|Node $node,
		public null|string $entryDescription,
		public SourceLocation|SourcePosition|array|null $location,
	) {}
}