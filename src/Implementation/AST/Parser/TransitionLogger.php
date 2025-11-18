<?php

namespace Walnut\Lang\Implementation\AST\Parser;

use Closure;
use Walnut\Lang\Blueprint\AST\Parser\ParserState as ParserStateInterface;
use Walnut\Lib\Walex\Token;

final class TransitionLogger {

	private array $steps = [];

	public function logStep(ParserStateInterface $s, Token $token, mixed $transition): void {
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
				$transition instanceof Closure ? '(fn)' : $transition);
		}
		return implode("\n", $lines);
	}
	// @codeCoverageIgnoreEnd
}