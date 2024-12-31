<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
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

	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry,
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		$resultType = $this->context->typeRegistry->alias(new TypeNameIdentifier('JsonValue'));
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

	private function getJsonValue(Value $value): Value {
		$result = $this->asJsonValue($value);
		return $result instanceof TypedValue ? $result->value : $result;
	}

	private function asJsonValue(Value $value): Value|TypedValue {
		if ($value instanceof TupleValue) {
			$items = [];
			foreach($value->values as $item) {
				$items[] = $this->getJsonValue($item);
			}
			return $this->context->valueRegistry->tuple($items);
		}
		if ($value instanceof RecordValue) {
			$items = [];
			foreach($value->values as $key => $item) {
				$items[$key] = $this->getJsonValue($item);
			}
			return $this->context->valueRegistry->record($items);
		}
		if ($value instanceof NullValue ||
			$value instanceof BooleanValue ||
			$value instanceof IntegerValue ||
			$value instanceof RealValue ||
			$value instanceof StringValue
		) {
			return $value;
		}
		$method = $this->methodRegistry->method(
			$value->type,
			new MethodNameIdentifier('asJsonValue')
		);
		if ($method instanceof Method && !($method instanceof self)) {
			return $method->execute(
				TypedValue::forValue($value), 
				TypedValue::forValue($this->context->valueRegistry->null)
			);
		}
		if ($value instanceof MutableValue) {
			return $this->getJsonValue($value->value);
		}
		if ($value instanceof SubtypeValue) {
			return $this->getJsonValue($value->baseValue);
		}
		if ($value instanceof SealedValue) {
			return $this->getJsonValue($value->value);
		}
		if ($value instanceof EnumerationValue) {
			return $this->context->valueRegistry->string($value->name->identifier);
		}
		throw new FunctionReturn($this->context->valueRegistry->error(
			$this->context->valueRegistry->sealedValue(
				new TypeNameIdentifier('InvalidJsonValue'),
				$this->context->valueRegistry->record(['value' => $value])
			)
		));
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		try {
			$result = $this->asJsonValue($targetValue);
		} catch (FunctionReturn $return) {
			return TypedValue::forValue($return->value);
		}
		return $result instanceof Value ? new TypedValue(
			$this->context->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			$result,
		) : $result;
	}

}