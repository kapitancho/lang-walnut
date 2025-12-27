<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\NoExternalErrorExpression as NoExternalErrorExpressionInterface;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Implementation\Type\Helper\ExternalTypeHelper;

final readonly class NoExternalErrorExpression implements NoExternalErrorExpressionInterface, JsonSerializable {
	use ExternalTypeHelper;

	public function __construct(
		public Expression $targetExpression
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$ret = $this->targetExpression->analyse($analyserContext);
		$expressionType = $ret->expressionType;
		if ($expressionType instanceof ResultType) {
			return $ret->withExpressionType(
				$this->withoutExternalError($analyserContext->programRegistry->typeRegistry, $expressionType)
			)->withReturnType(
				$analyserContext->programRegistry->typeRegistry->result(
					$ret->returnType,
					$analyserContext->programRegistry->typeRegistry->withName(
						new TypeNameIdentifier('ExternalError')
					)
				)
			);
		}
		return $ret;
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return $this->targetExpression->analyseDependencyType($dependencyContainer);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$result = $this->targetExpression->execute($executionContext);
		$value = $result->value;
		if ($value instanceof ErrorValue) {
			$errorValue = $value->errorValue;
			if ($errorValue instanceof SealedValue && $errorValue->type->name->equals(
				new TypeNameIdentifier('ExternalError'))
			) {
				throw new FunctionReturn($result->value);
			}
		}
		$vt = $result->value->type;
		// @codeCoverageIgnoreStart
		if ($vt instanceof ResultType) {
			$result = $result->withValue(
				$result->value
			);
		}
		// @codeCoverageIgnoreEnd
		return $result;
	}

	public function __toString(): string {
		return sprintf(
			"?noExternalError(%s)",
			$this->targetExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'noExternalError',
			'targetExpression' => $this->targetExpression
		];
	}
}