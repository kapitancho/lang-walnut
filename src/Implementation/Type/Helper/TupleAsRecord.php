<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
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
		return $tupleType->isSubtypeOf(
			$typeRegistry->tuple(
				array_slice(
					array_values($recordType->types),
					0,
					count($tupleType->types)
				)
			)
		);
	}

	private function adjustParameterValue(
		ValueRegistry               $valueRegistry,
		Type                        $expectedType,
		Value $actualValue,
	): Value {
		if ($actualValue instanceof TupleValue && $expectedType instanceof RecordType) {
			$actualValue = $this->getTupleAsRecord(
				$valueRegistry,
				$actualValue,
				$expectedType
			);
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
		foreach($recordType->types as $key => $rType) {
			try {
				$value = $tupleValue->valueOf($index++);
				$result[$key] = $value;
                // @codeCoverageIgnoreStart
			} catch (UnknownProperty $e) {
				if (!($rType instanceof OptionalKeyType)) {
					throw $e;
				}
			}
			// @codeCoverageIgnoreEnd
		}
		return $valueRegistry->record($result);
	}

}