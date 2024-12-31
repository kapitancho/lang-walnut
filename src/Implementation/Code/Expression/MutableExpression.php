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
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class MutableExpression implements MutableExpressionInterface, JsonSerializable {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		public Type $type,
		public Expression $value
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$analyserResult = $this->value->analyse($analyserContext);
		if ($analyserResult->expressionType->isSubtypeOf($this->type)) {
			return $analyserResult->withExpressionType(
				$this->typeRegistry->mutable($this->type)
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

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->value->execute($executionContext);
		if ($executionContext->valueType->isSubtypeOf($this->type)) {
			return $executionContext->withTypedValue(
				new TypedValue(
					$this->typeRegistry->mutable($this->type),
					$this->valueRegistry->mutable(
						$this->type,
						$executionContext->value
					)
				)
			);
		}
		throw new ExecutionException(
			sprintf(
				"%s Value type %s is not a subtype of %s",
				__CLASS__,
				$executionContext->valueType,
				$this->type
			)
		);
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