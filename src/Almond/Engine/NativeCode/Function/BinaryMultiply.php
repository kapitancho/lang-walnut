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
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\ExternalTypeHelper;

final readonly class BinaryMultiply extends FunctionCompose {
	use ExternalTypeHelper;

	protected function getValidator(): callable {
		return function(FunctionType $targetType, FunctionType $parameterType, mixed $origin): FunctionType|ValidationFailure {
			$exe = $this->typeRegistry->core->externalError;
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
			$firstCheckType = $fError && $exe->isSubtypeOf($fError) ?
				$this->withoutExternalError($this->typeRegistry, $r) : $r;
			$firstErrorType = $fError && $exe->isSubtypeOf($fError) ? $exe : null;

			if (!$firstCheckType->isSubtypeOf($parameterType->parameterType)) {
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
				$firstErrorType ? $this->typeRegistry->result(
					$parameterType->returnType,
					$firstErrorType
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
			$this->expressionRegistry->noExternalError(
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