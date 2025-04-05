<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use Walnut\Lang\Blueprint\AST\Builder\SourceLocator as SourceLocatorInterface;
use Walnut\Lang\Blueprint\AST\Parser\ParserState;
use Walnut\Lang\Implementation\AST\Node\SourceLocation;
use Walnut\Lib\Walex\SourcePosition;
use Walnut\Lib\Walex\Token;

final readonly class SourceLocator implements SourceLocatorInterface {

	/** @param Token[] $tokens */
	public function __construct(
		public string      $moduleName,
		public array       $tokens,
		public ParserState $state
	) {}

	public function getSourceLocation(): SourceLocation {
		$token = $this->tokens[$this->state->i] ?? null;
		$endPosition = $token?->sourcePosition ?? new SourcePosition(9999999, 9999, 9999);
		$startPosition = $this->state->result['startPosition'] ?? $endPosition;
		$len = strlen($token?->patternMatch->text ?? '-') - 1;
		return new SourceLocation(
			$this->moduleName,
			$startPosition,
			new SourcePosition($endPosition->offset + $len, $endPosition->line, $endPosition->column + $len)
		);
	}

}