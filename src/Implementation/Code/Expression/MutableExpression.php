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
use Walnut\Lang\Blueprint\Code\Expression\MutableExpression as MutableExpressionInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class MutableExpression implements MutableExpressionInterface, JsonSerializable {

	public function __construct(
		public Type $type,
		public Expression $value
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$analyserResult = $this->value->analyse($analyserContext);
		if ($analyserResult->expressionType->isSubtypeOf($this->type)) {
			return $analyserResult->withExpressionType(
				$analyserContext->programRegistry->typeRegistry->mutable($this->type)
			);
		}
		throw new AnalyserException(
			sprintf(
				"%s Value type %s is not a subtype of %s",
				__CLASS__,
				$analyserResult->expressionType,
				$this->type
			)
		);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->value->analyseDependencyType($dependencyContainer);
	}


	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->value->execute($executionContext);
		if ($executionContext->value->type->isSubtypeOf($this->type)) {
			return $executionContext->withValue(
				(
					$executionContext->programRegistry->valueRegistry->mutable(
						$this->type,
						$executionContext->value
					)
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException(
			sprintf(
				"%s Value type %s is not a subtype of %s",
				__CLASS__,
				$executionContext->value->type,
				$this->type
			)
		);
		// @codeCoverageIgnoreEnd
	}

	public function __toString(): string {
		return sprintf(
			"mutable{%s, %s}",
			$this->type,
			$this->value
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Mutable',
			'type' => $this->type,
			'value' => $this->value
		];
	}
}