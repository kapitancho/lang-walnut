<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\TupleValue;

trait TupleAsRecord {

	public function isTupleCompatibleToRecord(
		TypeRegistry $typeRegistry,
		TupleType $tupleType,
		RecordType $recordType
	): bool {
		return $tupleType->isSubtypeOf($typeRegistry->tuple(array_values($recordType->types)));
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