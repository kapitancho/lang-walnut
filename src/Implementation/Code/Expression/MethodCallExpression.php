<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression as MethodCallExpressionInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class MethodCallExpression implements MethodCallExpressionInterface, JsonSerializable {
	public function __construct(
		private TypeRegistry $typeRegistry,
		private MethodRegistry $methodRegistry,
		public Expression $target,
		public MethodNameIdentifier $methodName,
		public Expression $parameter,
	) {}

	private function getMethod($targetType): Method|UnknownMethod {
		return $this->methodRegistry->method($targetType,
			$this->methodName->identifier === 'as' ?
				new MethodNameIdentifier('castAs') :
				$this->methodName);
	}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$analyserContext = $this->target->analyse($analyserContext);
		$retExpr = $analyserContext->expressionType;

		//Special case: cast as - it requires the method registry and a dependency loop should be avoided.
		$method = $this->getMethod($retExpr);
		if ($method instanceof UnknownMethod) {
			throw new AnalyserException(
				sprintf(
					"Cannot call method '%s' on type '%s'",
					$this->methodName,
					$retExpr
				)
			);
		}
		$targetReturnType = $analyserContext->returnType;

		$analyserContext = $this->parameter->analyse($analyserContext);
		$retParamType = $analyserContext->expressionType;

		$retType = $method->analyse($retExpr, $retParamType);
		return $analyserContext->asAnalyserResult(
			$retType,
			$this->typeRegistry->union([
				$targetReturnType,
				$analyserContext->returnType
			])
		);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->target->execute($executionContext);
		$retTypedValue = $executionContext->typedValue;
		$retValue = $executionContext->value;
		$retType = $executionContext->valueType;

		$method = $this->getMethod($retValue->type);
		if ($method instanceof UnknownMethod) {
			$method = $this->methodRegistry->method($retType, $this->methodName);
			if ($method instanceof UnknownMethod) {
				// @codeCoverageIgnoreStart
				throw new ExecutionException(
					sprintf(
						"Cannot call method '%s' on type '%s' for value '%s' and parameter '%s'",
						$this->methodName,
						$retValue->type,
						$retValue,
						$this->parameter
					)
				);
				// @codeCoverageIgnoreEnd
			}
		}

		$executionContext = $this->parameter->execute($executionContext);
		$retParamType = $executionContext->valueType;

		$value = $method->execute($retTypedValue, $executionContext->typedValue);
		if ($value instanceof ErrorValue &&
			$value->errorValue->type instanceof SealedType &&
			$value->errorValue->type->name === 'DependencyContainerError'
		) {
			throw new FunctionReturn($value);
		}
		if ($value instanceof Value) {
			$valueType = $value->type;
			try {
				$valueType = $method->analyse($retType, $retParamType);
			} catch (AnalyserException) {}
			$value = new TypedValue($valueType, $value);
		}
		return $executionContext->asExecutionResult($value);
	}

	public function __toString(): string {
		$parameter = (string)$this->parameter;
		if (!($parameter[0] === '[' && $parameter[-1] === ']')) {
			$parameter = "($parameter)";
		}
		if ($parameter === '(null)') {
			$parameter = '';
		}
		return sprintf(
			"%s->%s%s",
			$this->target,
			$this->methodName,
			$parameter === '(null)' ? '' : $parameter
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'methodCall',
			'target' => $this->target,
			'methodName' => $this->methodName,
			'parameter' => $this->parameter
		];
	}
}