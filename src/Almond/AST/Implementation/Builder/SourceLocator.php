<?php

namespace Walnut\Lang\Almond\AST\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator as SourceLocatorInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Implementation\Parser\ParserState;
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
		$tokenX = $this->tokens[$this->state->i - 1] ?? null;
		if ($tokenX) {
			$newLines = substr_count($tokenX->patternMatch->text, "\n");
			$endPosition = $tokenX->sourcePosition;
			if ($newLines === 0) {
				$endPosition = $endPosition->move(strlen($tokenX->patternMatch->text));
			} else {
				for($i = 0; $i < $newLines; $i++) {
					$endPosition = $endPosition->goToNextLine();
				}
				$endPosition = $endPosition->move(
					strlen($tokenX->patternMatch->text) -
					strrpos($tokenX->patternMatch->text, "\n") - 1
				);
			}
		} else {
			$token = $this->tokens[$this->state->i] ?? null;
			$endPosition = $token->sourcePosition ?? new SourcePosition(9999999, 9999, 9999);
		}
		$startPosition = $this->state->result['startPosition'] ?? $endPosition;
		$len = strlen($token?->patternMatch->text ?? '-') - 1;
		return new SourceLocation(
			$this->moduleName,
			$startPosition,
			new SourcePosition($endPosition->offset + $len, $endPosition->line, $endPosition->column + $len)
		);
	}

}