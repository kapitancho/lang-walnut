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
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
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

	private function getJsonValue(ProgramRegistry $programRegistry, Value $value): Value {
		$result = $this->asJsonValue($programRegistry, $value);
		return $result instanceof TypedValue ? $result->value : $result;
	}

	private function asJsonValue(ProgramRegistry $programRegistry, Value $value): Value|TypedValue {
		if ($value instanceof TupleValue) {
			$items = [];
			foreach($value->values as $item) {
				$items[] = $this->getJsonValue($programRegistry, $item);
			}
			return $programRegistry->valueRegistry->tuple($items);
		}
		if ($value instanceof RecordValue) {
			$items = [];
			foreach($value->values as $key => $item) {
				$items[$key] = $this->getJsonValue($programRegistry, $item);
			}
			return $programRegistry->valueRegistry->record($items);
		}
		if ($value instanceof NullValue ||
			$value instanceof BooleanValue ||
			$value instanceof IntegerValue ||
			$value instanceof RealValue ||
			$value instanceof StringValue
		) {
			return $value;
		}
		$method = $programRegistry->methodRegistry->method(
			$value->type,
			new MethodNameIdentifier('asJsonValue')
		);
		if ($method instanceof Method && !($method instanceof self)) {
			return $method->execute(
				$programRegistry,
				TypedValue::forValue($value), 
				TypedValue::forValue($programRegistry->valueRegistry->null)
			);
		}
		if ($value instanceof MutableValue) {
			return $this->getJsonValue($programRegistry, $value->value);
		}
		if ($value instanceof SubtypeValue) {
			return $this->getJsonValue($programRegistry, $value->baseValue);
		}
		if ($value instanceof SealedValue) {
			return $this->getJsonValue($programRegistry, $value->value);
		}
		if ($value instanceof EnumerationValue) {
			return $programRegistry->valueRegistry->string($value->name->identifier);
		}
		throw new FunctionReturn($programRegistry->valueRegistry->error(
			$programRegistry->valueRegistry->openValue(
				new TypeNameIdentifier('InvalidJsonValue'),
				$programRegistry->valueRegistry->record(['value' => $value])
			)
		));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		try {
			$result = $this->asJsonValue($programRegistry, $targetValue);
		} catch (FunctionReturn $return) {
			return TypedValue::forValue($return->value);
		}
		return $result instanceof Value ? new TypedValue(
			$programRegistry->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			$result,
		) : $result;
	}

}