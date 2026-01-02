<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\GroupExpression as GroupExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;

final readonly class GroupExpression implements GroupExpressionInterface, JsonSerializable {

	public function __construct(
		public Expression $innerExpression
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		return $this->innerExpression->analyse($analyserContext);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->innerExpression->analyseDependencyType($dependencyContainer);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		return $this->innerExpression->execute($executionContext);
	}

	public function __toString(): string {
		return sprintf("(%s)", $this->innerExpression);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'group',
			'innerExpression' => $this->innerExpression
		];
	}
}