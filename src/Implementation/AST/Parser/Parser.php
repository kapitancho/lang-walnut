<?php

namespace Walnut\Lang\Implementation\AST\Parser;

use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilder;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Parser\Parser as ParserInterface;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lib\Walex\Token;

final readonly class Parser implements ParserInterface {
	public function __construct(
		private TransitionLogger $transitionLogger,
	) {}

    /**
     * @param Token[] $tokens
     * @throws ParserException
     */
	public function parseAndBuildCodeFromTokens(
		NodeBuilderFactory $nodeBuilderFactory,
		ModuleNodeBuilder $moduleNodeBuilder,
		array $tokens,
		string $moduleName
	): ModuleNode {
		$s = new ParserState;
		$s->push(-1);
		$s->state = 101;

		$nodeBuilder = $nodeBuilderFactory->newBuilder(
			$tokens,
			$s
		);

		$stateMachine = new ParserStateMachine(
			$s,
			$nodeBuilder,
			$moduleNodeBuilder
		);
		$states = $stateMachine->getAllStates();

		$l = count($tokens);
		$ctr = 0;
		while($s->i < $l) {
			$token = $tokens[$s->i];
			if (++$ctr > 20000) {
				throw new ParserException($s, "Recursion limit reached", $token, $moduleName);
			}
            $tag = is_string($token->rule->tag) ? $token->rule->tag :
                strtoupper($token->rule->tag->name);
			if ($tag === 'code_comment') {
				$s->i++;
				continue;
			}

			$matchingState = $states[$s->state] ?? null;
			$stateName = $matchingState['name'] ?? 'unknown(' . $s->state . ')';
			$transitions = $matchingState['transitions'] ?? [];
			$transition = $transitions[$tag] ?? $transitions[''] ?? null;
			$this->transitionLogger->logStep($s, $token, $transition);
			if (!$transition) {
				throw new ParserException($s,
                    sprintf("No transition found for token '%s' in state '%s'",
                        $tag,
                        $stateName
                    ), $token, $moduleName);
			}
			if (is_callable($transition)) {
				$lastI = $s->i;
				$lastState = $s->state;
					$transition($token, $s, $nodeBuilder);
				if ($s->i === $lastI && $s->state === $lastState) {
					throw new ParserException($s, "Transition did not change state or index ($lastI, $lastState)", $token, $moduleName);
				}
			} else {
				$t = (int)$transition;
				$s->state = abs($t);
				$s->i++;
				$startPos = $tokens[$s->i]->sourcePosition ?? null;
				if ($t < 0 && $startPos !== null) {
					$s->result['startPosition'] = $startPos;
				}
			}
		}
		return $moduleNodeBuilder->build();
	}
}