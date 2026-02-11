<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<FunctionType, FunctionType, FunctionValue, FunctionValue> */
abstract readonly class FunctionCompose extends NativeMethod {

	abstract protected function getCompositionExpression(): Expression;

	protected function getExecutor(): callable {
		return function(FunctionValue $target, FunctionValue $parameter): FunctionValue {
			$validationResult = $this->validate($target->type, $parameter->type, null);
			if ($validationResult instanceof ValidationFailure) {
				throw new ExecutionException("Invalid target or parameter type: " .
					$validationResult->errors[0]->message);
			}
			$returnType = $validationResult->type;
			if (!$returnType instanceof FunctionType) {
				throw new ExecutionException("Invalid return type");
			}
			return $this->functionValueFactory->function(
				new NameAndType($target->type, null),
				new NameAndType($this->typeRegistry->nothing, null),
				$returnType->returnType,
				$this->expressionRegistry->functionBody(
					$this->getCompositionExpression()
				)
			)->withVariableValueScope(
				$this->variableScopeFactory->emptyVariableValueScope
					->withAddedVariableValue(new VariableName('first'), $target)
					->withAddedVariableValue(new VariableName('second'), $parameter)
			);
		};
	}

}