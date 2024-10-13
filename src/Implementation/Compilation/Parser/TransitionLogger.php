<?php

namespace Walnut\Lang\Implementation\Compilation\Parser;

use Closure;
use Walnut\Lib\Walex\Token;

final class TransitionLogger {

	private array $steps = [];

	public function logStep(ParserState $s, Token $token, mixed $transition): void {
		$this->steps[] = [
            $s->i, $s->state, $token, $transition ?? 'n/a', $s->depth(), $token->rule->tag
        ];
	}

	public function __toString(): string {
		$lines = [];
		foreach($this->steps as [$i, $state, $token, $transition, $depth, $tag]) {
			$lines[] = sprintf("%3d %3d %3d %s %s %s", $depth, $i, $state,
                is_string($tag) ? $tag : $tag->name, $token->patternMatch->text,
				$transition instanceof Closure ? '(fn)' : $transition);
		}
		return implode("\n", $lines);
	}
}