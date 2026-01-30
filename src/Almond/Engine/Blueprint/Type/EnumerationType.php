<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue;

interface EnumerationType extends EnumerationSubsetType, NamedType {
	/** @param array<string, EnumerationValue> $values */
	public array $values { get; }

    /**
     * @param non-empty-list<EnumerationValueName> $values
     * @throws UnknownEnumerationValue|DuplicateSubsetValue|InvalidArgument
     **/
    public function subsetType(array $values): EnumerationSubsetType;

	/** @throws UnknownEnumerationValue **/
	public function value(EnumerationValueName $valueIdentifier): EnumerationValue;
}