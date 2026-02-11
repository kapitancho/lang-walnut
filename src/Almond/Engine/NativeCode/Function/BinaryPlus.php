<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\FunctionCompose;

final readonly class BinaryPlus extends FunctionCompose {

	protected function getValidator(): callable {
		return function(FunctionType $targetType, FunctionType $parameterType, mixed $origin): FunctionType|ValidationFailure {
			if (!$targetType->returnType->isSubtypeOf($parameterType->parameterType)) {
				return $this->validationFactory->error(
					ValidationErrorType::compositionMismatch,
					sprintf(
						"Cannot compose functions: return type %s of first function is not a subtype of parameter type %s of second function",
						$targetType->returnType,
						$parameterType->parameterType
					),
					$origin
				);
			}
			return $this->typeRegistry->function(
				$targetType->parameterType,
				$parameterType->returnType
			);
		};
	}

	protected function getCompositionExpression(): Expression {
		return $this->expressionRegistry->methodCall(
			$this->expressionRegistry->variableName(
				new VariableName('second')
			),
			new MethodName('invoke'),
			$this->expressionRegistry->methodCall(
				$this->expressionRegistry->variableName(
					new VariableName('first')
				),
				new MethodName('invoke'),
				$this->expressionRegistry->variableName(
					new VariableName('#')
				),
			)
		);
	}

}