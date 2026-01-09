<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ErrorAsExternal implements NativeMethod {
	use BaseType;

	private Type $externalErrorType;

	private function externalErrorType(TypeRegistry $typeRegistry): Type {
		return $this->externalErrorType ??= $typeRegistry->core->externalError;
	}

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$target = $this->toBaseType($targetType);
		if ($parameterType->isSubtypeOf(
			$typeRegistry->union([
				$typeRegistry->null,
				$typeRegistry->string(),
			])
		)) {
			return $target instanceof ResultType ?
				$typeRegistry->result($target->returnType, $this->externalErrorType(
					$typeRegistry
				)) :
				$target;
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof ErrorValue) {
			$errorValue = $target->errorValue;
			if (!($errorValue instanceof SealedValue && $errorValue->type->name->equals(
				CoreType::ExternalError->typeName()
			))) {
				$errorMessage = $parameter instanceof StringValue ? $parameter :
					$programRegistry->valueRegistry->string('Error');

				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->core->externalError(
						$programRegistry->valueRegistry->record([
							'errorType' => $programRegistry->valueRegistry->string((string)$errorValue->type),
							'originalError' => $target,
							'errorMessage' => $errorMessage
						])
					)
				);
			}
		}
		return $target;
	}

}