<?php

namespace Walnut\Lang\Blueprint\Type;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Program\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;

interface EnumerationType extends EnumerationSubsetType, NamedType {
    /** @return array<string, EnumerationValue> */
    public function values(): array;

    /**
     * @param non-empty-list<EnumValueIdentifier> $values
     * @throws UnknownEnumerationValue|InvalidArgumentException
     **/
    public function subsetType(array $values): EnumerationSubsetType;

	/** @throws UnknownEnumerationValue **/
	public function value(EnumValueIdentifier $valueIdentifier): EnumerationValue;
}