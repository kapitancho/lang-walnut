<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class JsonStringify implements NativeMethod {

	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry,
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$resultType = $this->context->typeRegistry->string();
		return $this->isSafeConversion($targetType) ? $resultType : $this->context->typeRegistry->result(
			$resultType,
			$this->context->typeRegistry->withName(new TypeNameIdentifier('InvalidJsonValue'))
		);
	}

	private function isSafeConversion(Type $fromType): bool {
		return $fromType->isSubtypeOf(
			$this->context->typeRegistry->withName(new TypeNameIdentifier('JsonValue'))
		);
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$method0 = $this->methodRegistry->method(
			$targetValue->type, new MethodNameIdentifier('stringify')
		);
		if ($method0 !== UnknownMethod::value) {
			return $method0->execute($target, $parameter);
		}

		$method1 = $this->methodRegistry->method(
			$targetValue->type, new MethodNameIdentifier('asJsonValue')
		);
		$step1 = $method1->execute($target, $parameter);
		$method2 = $this->methodRegistry->method(
			$this->context->typeRegistry->alias(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier('stringify')
		);
		return $method2->execute($step1, $parameter);
	}

}