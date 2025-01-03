<?php

namespace Walnut\Lang\Implementation\Compilation\Parser;

use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilder;
use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilder;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Compilation\Parser as ParserInterface;
use Walnut\Lang\Blueprint\Function\FunctionBodyException;
use Walnut\Lang\Blueprint\Program\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Range\InvalidIntegerRange;
use Walnut\Lang\Blueprint\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Range\InvalidRealRange;
use Walnut\Lib\Walex\Token;

final readonly class Parser implements ParserInterface {
	public function __construct(
		private TransitionLogger $transitionLogger,
	) {}

    /** @param Token[] $tokens */
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
					$transition($token, $s, $nodeBuilder);
				} catch (InvalidIntegerRange|InvalidRealRange|InvalidLengthRange $e) {
					throw new ParserException($s, "Range Issue: " . $e->getMessage(), $token, $moduleName);
				} catch (UnknownEnumerationValue $e) {
					throw new ParserException($s, "Enumeration Issue: " . $e->getMessage(), $token, $moduleName);
				} catch (UnknownType $e) {
					throw new ParserException($s, "Unknown type: " . $e->getMessage(), $token, $moduleName);
                } catch (FunctionBodyException|UnknownContextVariable $e) {
                    throw new ParserException($s, $e->getMessage(), $token, $moduleName);
				} catch (CompilationException $e) {
					throw new ParserException($s, "Compilation exception: " . $e->getMessage(), $token, $moduleName);
				} catch (AnalyserException $e) {
					throw new ParserException($s, "Analyser exception: " . $e->getMessage(), $token, $moduleName);
				}
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