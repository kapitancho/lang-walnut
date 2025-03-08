<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\TupleValue;

trait TupleAsRecord {

	private function adjustParameterType(
		TypeRegistry $typeRegistry,
		Type $expectedType,
		Type $actualType,
	): Type {
		return
			$expectedType instanceof RecordType &&
			$actualType instanceof TupleType &&
			$this->isTupleCompatibleToRecord(
				$typeRegistry,
				$actualType,
				$expectedType
			) ? $expectedType : $actualType;
	}

	public function isTupleCompatibleToRecord(
		TypeRegistry $typeRegistry,
		TupleType $tupleType,
		RecordType $recordType
	): bool {
		return $tupleType->isSubtypeOf($typeRegistry->tuple(array_values($recordType->types)));
	}

	private function adjustParameterValue(
		ValueRegistry $valueRegistry,
		Type $expectedType,
		TypedValue|null $actualValue,
	): TypedValue|null {
		if ($actualValue === null) {
			return null;
		}
		if ($actualValue->value instanceof TupleValue && $expectedType instanceof RecordType) {
			$actualValue = TypedValue::forValue(
				$this->getTupleAsRecord(
					$valueRegistry,
					$actualValue->value,
					$expectedType
				)
			)->withType($expectedType);
		}
		return $actualValue;
	}

	private function getTupleAsRecord(
		ValueRegistry $valueRegistry,
		TupleValue    $tupleValue,
		RecordType    $recordType
	): RecordValue {
		$result = [];
		$index = 0;
		foreach($recordType->types as $key => $value) {
			$result[$key] = $tupleValue->valueOf($index++);
		}
		return $valueRegistry->record($result);
	}

}