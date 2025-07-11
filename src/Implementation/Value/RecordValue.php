<?php

namespace Walnut\Lang\Implementation\Value;

use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\RecordValue as RecordValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class RecordValue implements RecordValueInterface, JsonSerializable {

	private readonly RecordType $actualType;

	/**
	 * @param TypeRegistry $typeRegistry
	 * @param array<string, Value> $values
	 */
    public function __construct(
        private readonly TypeRegistry $typeRegistry,
	    public readonly array $values
    ) {
	    foreach($this->values as $value) {
            if (!$value instanceof Value) {
                throw new InvalidArgumentException(
                    'RecordValue must be constructed with a list of Value instances'
                );
            }
        }
    }

	public RecordType $type {
        get => $this->actualType ??= $this->typeRegistry->record(
			array_map(
				static fn(Value $value): Type =>
					$value->type, $this->values
			),
	        $this->typeRegistry->nothing
        );
    }

	/** @throws UnknownProperty */
	public function valueOf(string $propertyName): Value {
		return $this->values[$propertyName] ??
			throw new UnknownProperty($propertyName, (string)$this);
	}

	public function equals(Value $other): bool {
		if ($other instanceof RecordValueInterface) {
			$thisValues = $this->values;
			$otherValues = $other->values;
			if (count($thisValues) === count($otherValues)) {
				return array_all($thisValues, fn($value, $key) => array_key_exists($key, $otherValues) && $value->equals($otherValues[$key]));
			}
		}
		return false;
	}

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {
		foreach ($this->values as $value) {
			$value->selfAnalyse($analyserContext);
		}
	}

	public function asString(bool $multiline): string {
		if (count($this->values) === 0) {
			return '[:]';
		}
		return sprintf(
			$multiline ? "[\n\t%s\n]" : "[%s]",
			implode(
				$multiline ? ',' . "\n" . "\t" : ', ',
				array_map(
					static fn(string $key, Value $value): string =>
						($s = sprintf("%s: %s", $key, $value)) && $multiline ?
							str_replace("\n", "\n" . "\t", $s) : $s,
					array_keys($this->values), $this->values
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
			'valueType' => 'Record',
			'value' => $this->values
		];
	}

}