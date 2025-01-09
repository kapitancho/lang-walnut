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
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\SealedValue;

final readonly class NoExternalErrorExpression implements NoExternalErrorExpressionInterface, JsonSerializable {
	public function __construct(
		public Expression $targetExpression
	) {}

	private function withoutExternalError(TypeRegistry $typeRegistry, ResultType $resultType): Type {
		$errorType = $resultType->errorType;
		$errorType = match(true) {
			$errorType instanceof SealedType && $errorType->name->equals(
				new TypeNameIdentifier('ExternalError')) => $typeRegistry->nothing,
			$errorType instanceof UnionType => $typeRegistry->union(
				array_filter($errorType->types, static fn(Type $t): bool => !(
					$t instanceof SealedType && $t->name->equals(
						new TypeNameIdentifier('ExternalError')
					)
				))
			),
			default => $errorType
		};
		return $errorType instanceof NothingType ? $resultType->returnType :
			$typeRegistry->result(
				$resultType->returnType,
				$errorType
			);
	}

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

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$result = $this->targetExpression->execute($executionContext);
		$value = $result->value;
		if ($value instanceof ErrorValue) {
			$errorValue = $value->errorValue;
			if ($errorValue instanceof SealedValue && $errorValue->type->name->equals(
				new TypeNameIdentifier('ExternalError'))
			) {
				throw new FunctionReturn($value);
			}
		}
		$vt = $result->valueType;
		if ($vt instanceof ResultType) {
			$result = $result->withTypedValue(new TypedValue(
				$this->withoutExternalError($executionContext->programRegistry->typeRegistry, $vt), $result->value));
		}
		return $result;
	}

	public function __toString(): string {
		return sprintf(
			"?noError(%s)",
			$this->targetExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'noError',
			'targetExpression' => $this->targetExpression
		];
	}
}