<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\NativeCode\Any\AsJsonValue;

final readonly class CastAsJsonValue {

	private ValueRegistry $valueRegistry;
	private MethodFinder $methodFinder;

	public function __construct(
		private ProgramRegistry $programRegistry,
	) {
		$this->valueRegistry = $programRegistry->valueRegistry;
		$this->methodFinder = $programRegistry->methodFinder;
	}

	public function getJsonValue(Value $value): Value {
		if ($value instanceof TupleValue) {
			$items = [];
			foreach($value->values as $item) {
				$items[] = $this->getJsonValue($item);
			}
			return $this->valueRegistry->tuple($items);
		}
		if ($value instanceof RecordValue) {
			$items = [];
			foreach($value->values as $key => $item) {
				$items[$key] = $this->getJsonValue($item);
			}
			return $this->valueRegistry->record($items);
		}
		if ($value instanceof NullValue ||
			$value instanceof BooleanValue ||
			$value instanceof IntegerValue ||
			$value instanceof RealValue ||
			$value instanceof StringValue
		) {
			return $value;
		}
		$method = $this->methodFinder->methodForType(
			$value->type,
			new MethodNameIdentifier('asJsonValue')
		);
		if ($method instanceof Method && !($method instanceof AsJsonValue)) {
			return $method->execute(
				$this->programRegistry,
				($value),
				($this->valueRegistry->null)
			);
		}
		if ($value instanceof MutableValue || $value instanceof OpenValue || $value instanceof SealedValue) {
			return $this->getJsonValue($value->value);
		}
		if ($value instanceof EnumerationValue) {
			return $this->valueRegistry->string($value->name->identifier);
		}
		throw new FunctionReturn((
			$this->valueRegistry->error(
				$this->valueRegistry->openValue(
					new TypeNameIdentifier('InvalidJsonValue'),
					$this->valueRegistry->record(['value' => $value])
				)
			)
		));
	}

}