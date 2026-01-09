<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class JsonStringify implements NativeMethod {

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$resultType = $typeRegistry->string();
		return $this->isSafeConversion($typeRegistry, $targetType) ? $resultType : $typeRegistry->result(
			$resultType,
			$typeRegistry->withName(new TypeNameIdentifier('InvalidJsonValue'))
		);
	}

	private function isSafeConversion(TypeRegistry $typeRegistry, Type $fromType): bool {
		return $fromType->isSubtypeOf(
			$typeRegistry->withName(new TypeNameIdentifier('JsonValue'))
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$step0 = $programRegistry->methodContext->safeExecuteMethod(
			$target,
			new MethodNameIdentifier('stringify'),
			$parameter
		);
		if ($step0 !== UnknownMethod::value) {
			return $step0;
		}

		$step1 = $programRegistry->methodContext->executeMethod(
			$target,
			new MethodNameIdentifier('asJsonValue'),
			$parameter
		);
		if ($step1 instanceof ErrorValue) {
			return $step1;
		}
		return $programRegistry->methodContext->executeMethod(
			$step1,
			new MethodNameIdentifier('stringify'),
			$parameter
		);
	}

}