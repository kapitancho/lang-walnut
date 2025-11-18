<?php

namespace Walnut\Lang\Implementation\Value;

use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SetValue as SetValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class SetValue implements SetValueInterface, JsonSerializable {

	private readonly SetType $actualType;
	/** @var list<Value> */
	public readonly array $values;
	/** @var array<string, Value> */
	public readonly array $valueSet;

	/**
	 * @param TypeRegistry $typeRegistry
	 * @param list<Value> $values
	 */
    public function __construct(
        private readonly TypeRegistry $typeRegistry,
	    array $values
    ) {
		$set = [];
		foreach($values as $value) {
			/** @phpstan-ignore-next-line instanceof.alwaysTrue */
			if (!$value instanceof Value) {
				// @codeCoverageIgnoreStart
				throw new InvalidArgumentException(
					'SetValue must be constructed with a list of Value instances'
				);
				// @codeCoverageIgnoreEnd
			}
			$set[(string)$value] = $value;
		}
		$this->valueSet = $set;
		$this->values = array_values($set);
    }

	public SetType $type {
		get => $this->actualType ??= $this->typeRegistry->set(
			$this->typeRegistry->union(
				array_map(
					static fn(Value $value): Type =>
						$value->type, $this->values
				)
			),
			count($this->values),
			count($this->values)
        );
    }

	public function equals(Value $other): bool {
		if ($other instanceof SetValueInterface) {
			$thisValues = $this->valueSet;
			$otherValues = $other->valueSet;
			if (count($thisValues) === count($otherValues)) {
				return array_all(array_keys($thisValues),
					fn(string $key) => array_key_exists($key, $otherValues));
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
		return match(count($this->values)) {
			0 => '[;]',
			1 => sprintf(
				"[%s;]",
				$this->values[0]
			),
			default => sprintf(
				$multiline ? "[\n\t%s\n]" : "[%s]",
				implode(
					$multiline ? ';' . "\n" . "\t" : '; ',
					array_map(
						static fn(Value $value): string => $multiline ?
							str_replace("\n", "\n" . "\t", $value) : $value,
	                        $this->values
					)
				)
			)
		};
	}

	public function __toString(): string {
		$result = $this->asString(false);
		return mb_strlen($result) > 40 ? $this->asString(true) : $result;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Set',
			'value' => $this->values
		];
	}
}