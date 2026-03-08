<?php

namespace Walnut\Lang\Almond\AST\Implementation\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserState;
use Walnut\Lib\Walex\SpecialRuleTag;
use Walnut\Lib\Walex\Token;

final class TransitionLogger {

	/**
	 * @var list<array{int, int, Token, string|int|(callable(Token, ParserState): void), int, string|SpecialRuleTag}>
	 */
	private array $steps = [];

	// @codeCoverageIgnoreStart
	public function clear(): void {
		$this->steps = [];
	}
	// @codeCoverageIgnoreEnd

	public function logStep(ParserState $s, Token $token, int|callable|null $transition): void {
		$this->steps[] = [
            $s->i, $s->state, $token, $transition ?? 'n/a', $s->depth(), $token->rule->tag
        ];
	}

	// @codeCoverageIgnoreStart
	public function __toString(): string {
		$lines = [];
		foreach($this->steps as [$i, $state, $token, $transition, $depth, $tag]) {
			$lines[] = sprintf("%3d %3d %3d %s %s %s", $depth, $i, $state,
                is_string($tag) ? $tag : $tag->name, $token->patternMatch->text,
				is_callable($transition) ? '(fn)' : $transition);
		}
		return implode("\n", $lines);
	}
	// @codeCoverageIgnoreEnd
}