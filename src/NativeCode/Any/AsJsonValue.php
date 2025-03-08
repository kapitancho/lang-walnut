<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\CastAsJsonValue;
use Walnut\Lang\Implementation\Value\EnumerationValue;

final readonly class AsJsonValue implements NativeMethod {

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		$resultType = $programRegistry->typeRegistry->alias(new TypeNameIdentifier('JsonValue'));
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

		try {
			$result = new CastAsJsonValue($programRegistry)
				->getJsonValue($targetValue);
		} catch (FunctionReturn $return) {
			return $return->typedValue;
		}
		return TypedValue::forValue($result)
			->withType($programRegistry->typeRegistry->withName(
				new TypeNameIdentifier('JsonValue')
			));
	}

}