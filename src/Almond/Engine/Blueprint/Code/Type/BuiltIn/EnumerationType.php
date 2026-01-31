<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;

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