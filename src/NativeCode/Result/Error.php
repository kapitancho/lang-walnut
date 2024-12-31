<?php

namespace Walnut\Lang\NativeCode\Result;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Error implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$target = $this->toBaseType($targetType);
		if ($target instanceof ResultType && $target->returnType instanceof NothingType) {
			return $target->errorType;
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof ErrorValue) {
			return new TypedValue(
				$this->context->typeRegistry->result(
					$this->context->typeRegistry->nothing,
					$targetValue->type
				),
				$targetValue->errorValue
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}