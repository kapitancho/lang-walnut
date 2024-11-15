<?php

namespace Walnut\Lang\Implementation\Compilation\Parser;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Compilation\CodeBuilder;
use Walnut\Lang\Blueprint\Compilation\ModuleImporter;
use Walnut\Lang\Blueprint\Compilation\Parser as ParserInterface;
use Walnut\Lang\Blueprint\Function\FunctionBodyException;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lib\Walex\Token;

final readonly class Parser implements ParserInterface {
	public function __construct(
		private TransitionLogger $transitionLogger,
	) {}

    /** @param Token[] $tokens */
	public function parseAndBuildCodeFromTokens(
		ModuleImporter $moduleImporter,
		CodeBuilder $codeBuilder,
		array $tokens,
		string $moduleName
	): mixed {
		$s = new ParserState;
		$s->push(-1);
		$s->state = 101;

		$stateMachine = new ParserStateMachine(
			$s,
			$codeBuilder,
			$moduleImporter
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

			$matchingState = $states[$s->state];
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
				try {
					$transition($token, $s, $codeBuilder);
				} catch (UnknownType $e) {
					throw new ParserException($s, "Unknown type: " . $e->getMessage(), $token, $moduleName);
                } catch (FunctionBodyException|UnknownContextVariable $e) {
                    throw new ParserException($s, $e->getMessage(), $token, $moduleName);
				} catch (AnalyserException $e) {
					throw new ParserException($s, "Analyser exception: " . $e->getMessage(), $token, $moduleName);
				}
				if ($s->i === $lastI && $s->state === $lastState) {
					throw new ParserException($s, "Transition did not change state or index ($lastI, $lastState)", $token, $moduleName);
				}
			} else {
				$s->state = (int)$transition;
				$s->i++;
			}
		}
		return $s->generated;
	}
}