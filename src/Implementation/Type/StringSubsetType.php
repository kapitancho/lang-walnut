<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\StringSubsetType as StringSubsetTypeInterface;
use Walnut\Lang\Blueprint\Type\StringType as StringTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Common\Range\LengthRange;

final class StringSubsetType implements StringSubsetTypeInterface, JsonSerializable {

	private readonly LengthRange $actualRange;

	/** @param list<string> $subsetValues */
    public function __construct(
		private readonly EscapeCharHandler $escapeCharHandler,
        public readonly array $subsetValues
    ) {
	    if ($subsetValues === []) {
		    // @codeCoverageIgnoreStart
		    throw new InvalidArgumentException("Cannot create an empty subset type");
		    // @codeCoverageIgnoreEnd
	    }
	    $selected = [];
	    foreach($subsetValues as $value) {
		    if (!is_string($value)) {
			    // @codeCoverageIgnoreStart
			    throw new InvalidArgumentException(
				    sprintf("Invalid value: '%s'", $value)
			    );
			    // @codeCoverageIgnoreEnd
		    }
		    if (array_key_exists($value, $selected)) {
			    DuplicateSubsetValue::ofString(
				    sprintf("String[%s]",
					    implode(', ',
						    array_map(
								fn(string $value) => $this->escapeCharHandler->escape($value),
							    $subsetValues
						    ))),
				    $value);
		    }
		    $selected[$value] = true;
	    }
    }

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof StringTypeInterface =>
                self::isInRange($this->subsetValues, $ofType->range),
            $ofType instanceof StringSubsetTypeInterface =>
                self::isSubset($this->subsetValues, $ofType->subsetValues),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	/** @param list<string> $subsetValues */
    private static function isInRange(array $subsetValues, LengthRange $range): bool {
	    return array_all($subsetValues, fn($value) => $range->lengthInRange(
			new Number(mb_strlen($value))
	    ));
    }

    private static function isSubset(array $subset, array $superset): bool {
	    return array_all($subset, fn($value) => in_array($value, $superset));
    }

	public function contains(StringValue $value): bool {
		return in_array($value->literalValue, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("String[%s]", implode(', ', array_map(
			fn(string $value): string => $this->escapeCharHandler->escape($value),
			$this->subsetValues
		)));
	}

	private function minLength(): Number {
		return new Number(min(array_map(
			static fn(string $value): int =>
				mb_strlen($value), $this->subsetValues
		)));
	}

	private function maxLength(): Number {
		return new Number(max(array_map(
			static fn(string $value): int =>
				mb_strlen($value), $this->subsetValues
		)));
	}

	public LengthRange $range {
		get => $this->actualRange ??= new LengthRange(
			$this->minLength(), $this->maxLength()
		);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'StringSubset',
			'values' => $this->subsetValues
		];
	}
}