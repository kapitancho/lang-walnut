<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;

final readonly class JsonStringify implements NativeMethod {

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$resultType = $programRegistry->typeRegistry->string();
		return $this->isSafeConversion($programRegistry->typeRegistry, $targetType) ? $resultType : $programRegistry->typeRegistry->result(
			$resultType,
			$programRegistry->typeRegistry->withName(new TypeNameIdentifier('InvalidJsonValue'))
		);
	}

	private function isSafeConversion(TypeRegistry $typeRegistry, Type $fromType): bool {
		return $fromType->isSubtypeOf(
			$typeRegistry->withName(new TypeNameIdentifier('JsonValue'))
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$method0 = $programRegistry->methodFinder->methodForType(
			$targetValue->type, new MethodNameIdentifier('stringify')
		);
		if ($method0 !== UnknownMethod::value) {
			return $method0->execute($programRegistry, $target, $parameter);
		}

		$method1 = $programRegistry->methodFinder->methodForType(
			$targetValue->type, new MethodNameIdentifier('asJsonValue')
		);
		$step1 = $method1->execute($programRegistry, $target, $parameter);
		if ($step1->value instanceof ErrorValue) {
			return $step1;
		}
		$method2 = $programRegistry->methodFinder->methodForType(
			$programRegistry->typeRegistry->alias(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier('stringify')
		);
		return $method2->execute($programRegistry, $step1, $parameter);
	}

}