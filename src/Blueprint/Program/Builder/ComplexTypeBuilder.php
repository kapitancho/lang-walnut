<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Implementation\Type\AliasType;
use Walnut\Lang\Implementation\Type\AtomType;
use Walnut\Lang\Implementation\Type\DataType;
use Walnut\Lang\Implementation\Type\EnumerationType;
use Walnut\Lang\Implementation\Type\OpenType;
use Walnut\Lang\Implementation\Type\SealedType;

interface ComplexTypeBuilder {
	public function atom(TypeNameIdentifier $name, AtomValue $value): AtomType;
	/**
	 * @param list<EnumerationValue> $values
	 * @throws DuplicateSubsetValue
	 **/
	public function enumeration(TypeNameIdentifier $name, array $values): EnumerationType;
	public function alias(TypeNameIdentifier $name, Type $aliasedType): AliasType;
	public function data(TypeNameIdentifier $name, Type $valueType): DataType;
	public function open(TypeNameIdentifier $name, Type $valueType): OpenType;
	public function sealed(TypeNameIdentifier $name, Type $valueType): SealedType;
}