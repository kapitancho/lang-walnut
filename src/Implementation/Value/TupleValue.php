<?php

namespace Walnut\Lang\Implementation\Value;

use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\TupleValue as TupleValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class TupleValue implements TupleValueInterface, JsonSerializable {

	private TupleType $type;

	/**
	 * @param TypeRegistry $typeRegistry
	 * @param list<Value> $values
	 */
    public function __construct(
        private TypeRegistry $typeRegistry,
	    private array $values
    ) {
		foreach($this->values as $value) {
			if (!$value instanceof Value) {
				throw new InvalidArgumentException(
					'TupleValue must be constructed with a list of Value instances'
				);
			}
		}
    }

    public function type(): TupleType {
        return $this->type ??= $this->typeRegistry->tuple(
			array_map(
				static fn(Value $value): Type =>
					$value->type(), $this->values
			),
	        $this->typeRegistry->nothing()
        );
    }

	/** @return list<Value> */
	public function values(): array {
		return $this->values;
	}

	/** @throws UnknownProperty */
	public function valueOf(int $index): Value {
		return $this->values[$index] ??
			throw new UnknownProperty((string)$index, (string)$this);
	}

	public function equals(Value $other): bool {
		if ($other instanceof TupleValueInterface) {
			$thisValues = $this->values;
			$otherValues = $other->values();
			if (count($thisValues) === count($otherValues)) {
				foreach($thisValues as $index => $value) {
					if (!$value->equals($otherValues[$index])) {
						return false;
					}
				}
				return true;
			}
		}
		return false;
	}


	public function asString(bool $multiline): string {
		if (count($this->values) === 0) {
			return '[]';
		}
		return sprintf(
			$multiline ? "[\n\t%s\n]" : "[%s]",
			implode(
				$multiline ? ',' . "\n" . "\t" : ', ',
				array_map(
					static fn(Value $value): string => $multiline ?
						str_replace("\n", "\n" . "\t", $value) : $value,
                        $this->values
				)
			)
		);
	}

	public function __toString(): string {
		$result = $this->asString(false);
		return mb_strlen($result) > 40 ? $this->asString(true) : $result;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'List',
			'value' => $this->values
		];
	}
}