<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;

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
				),
				null
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