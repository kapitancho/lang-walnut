<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\FunctionCompose;

final readonly class BinaryBitwiseAnd extends FunctionCompose {

	protected function getValidator(): callable {
		return function(FunctionType $targetType, FunctionType $parameterType, mixed $origin): FunctionType|ValidationFailure {
			$r = $targetType->returnType;
			$fReturn = match(true) {
				$r instanceof ResultType => $r->returnType,
				default => $r,
			};
			$fError = match(true) {
				$r instanceof AnyType => $r,
				$r instanceof ResultType => $r->errorType,
				default => null,
			};

			if (!$fReturn->isSubtypeOf($parameterType->parameterType)) {
				return $this->validationFactory->error(
					ValidationErrorType::compositionMismatch,
					sprintf(
						"Cannot compose functions: return type %s of first function is not a subtype of parameter type %s of second function",
						$fReturn, //$targetType->returnType,
						$parameterType->parameterType
					),
					$origin
				);
			}
			return $this->typeRegistry->function(
				$targetType->parameterType,
				$fError ? $this->typeRegistry->result(
					$parameterType->returnType,
					$fError
				) : $parameterType->returnType
			);
		};
	}

	protected function getCompositionExpression(): Expression {
		return $this->expressionRegistry->methodCall(
			$this->expressionRegistry->variableName(
				new VariableName('second')
			),
			new MethodName('invoke'),
			$this->expressionRegistry->noError(
				$this->expressionRegistry->methodCall(
					$this->expressionRegistry->variableName(
						new VariableName('first')
					),
					new MethodName('invoke'),
					$this->expressionRegistry->variableName(
						new VariableName('#')
					),
				)
			)
		);
	}

}