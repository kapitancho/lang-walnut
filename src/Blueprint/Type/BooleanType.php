<?php

namespace Walnut\Lang\Blueprint\Type;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Program\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Value\BooleanValue;

interface BooleanType extends EnumerationType {
	public BooleanType $enumeration { get; }
	/** @param array<string, BooleanType> $subsetValues */
	public array $subsetValues { get; }

	/** @param array<string, BooleanValue> $values */
	public array $values { get; }

	/**
	* @param non-empty-list<EnumValueIdentifier> $values
	* @throws UnknownEnumerationValue|InvalidArgumentException
	**/
	public function subsetType(array $values): TrueType|FalseType|BooleanType;

	/** @throws UnknownEnumerationValue **/
	public function value(EnumValueIdentifier $valueIdentifier): BooleanValue;
}