<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\BooleanNotExpression as BooleanNotExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;

final readonly class BooleanNotExpression implements BooleanNotExpressionInterface, JsonSerializable {

	use BaseTypeHelper;
	use BooleanExpressionHelper;

	public function __construct(
		public Expression $expression
	) {}

	/** @throws AnalyserException */
	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$analyserResult = $this->expression->analyse($analyserContext);

		$expressionType = $this->getBooleanType(
			$analyserContext,
			$analyserResult->expressionType
		);

		return $analyserResult->withExpressionType(match(true) {
			$expressionType instanceof FalseType => $analyserContext->typeRegistry->true,
			$expressionType instanceof TrueType => $analyserContext->typeRegistry->false,
			default => $analyserContext->typeRegistry->boolean
		});
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->expression->analyseDependencyType($dependencyContainer);
	}

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->expression->execute($executionContext);

		return $executionContext->withValue(
			$executionContext->valueRegistry->boolean(
				!$this->getBooleanValue($executionContext, $executionContext->value)
			)
		);
	}

	public function __toString(): string {
		return sprintf(
			"!{%s}",
			$this->expression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'BooleanNot',
			'first' => $this->expression
		];
	}
}