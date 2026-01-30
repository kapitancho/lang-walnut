<?php

namespace Walnut\Lang\Almond\AST\Implementation\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Builder\NodeBuilderFactory;
use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserStateRunner as ParserStateRunnerInterface;

final readonly class ParserStateRunner implements ParserStateRunnerInterface {
	public function __construct(
		private TransitionLogger $transitionLogger,
		private NodeBuilderFactory $nodeBuilderFactory,
	) {}

	public function run(SourceLocator $sourceLocator): ModuleNode {
		$s = $sourceLocator->state;
		$nodeBuilder = $this->nodeBuilderFactory->newBuilder($sourceLocator);
		$stateMachine = new ParserStateMachine(
			$s,
			$nodeBuilder,
			new StringEscapeCharHandler(),
			new BytesEscapeCharHandler()
		);
		$states = $stateMachine->getAllStates();

		$tokens = $sourceLocator->tokens;
		$l = count($tokens);
		$ctr = 0;
		$stateRepeatProtection = 0;
		while($s->i < $l) {
			$token = $tokens[$s->i];
			if (++$ctr > 200000) {
				// @codeCoverageIgnoreStart
				throw new ParserException($s, "Recursion limit reached", $token, $sourceLocator->moduleName);
				// @codeCoverageIgnoreEnd
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
                    ), $token, $sourceLocator->moduleName
				);
			}
			if (is_callable($transition)) {
				$lastI = $s->i;
				$lastState = $s->state;
				$transition($token, $s);
				if ($s->i === $lastI && $s->state === $lastState) {
					// @codeCoverageIgnoreStart
					if ($stateRepeatProtection++ > 10) {
						throw new ParserException($s, "Transition did not change state or index ($lastI, $lastState)", $token, $sourceLocator->moduleName);
					}
					// @codeCoverageIgnoreEnd
				} else {
					$stateRepeatProtection = 0;
				}
			} else {
				$t = (int)$transition;
				$s->state = abs($t);
				$startPos = $tokens[$s->i]->sourcePosition ?? null;
				$s->i++;
				//$startPos = $tokens[$s->i]->sourcePosition ?? null;
				if ($t < 0 && $startPos !== null) {
					$s->result['startPosition'] = $startPos;
				}
			}
		}
		return $nodeBuilder->build();
	}
}