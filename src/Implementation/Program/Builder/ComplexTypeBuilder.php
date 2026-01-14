<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\ComplexTypeBuilder as ComplexTypeBuilderInterface;
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

final readonly class ComplexTypeBuilder implements ComplexTypeBuilderInterface {

	public function atom(TypeNameIdentifier $name, AtomValue $value): AtomType {
		return new AtomType($name, $value);
	}

	/**
	 * @param list<EnumerationValue> $values
	 * @throws DuplicateSubsetValue
	 **/
	public function enumeration(TypeNameIdentifier $name, array $values): EnumerationType {
		$keys = [];
		foreach ($values as $value) {
			/** @phpstan-ignore-next-line identical.alwaysTrue */
			if (!$value instanceof EnumerationValue) {
				// @codeCoverageIgnoreStart
				throw new InvalidArgumentException(
					'Expected EnumerationValue, got ' . get_debug_type($value)
				);
				// @codeCoverageIgnoreEnd
			}
			if (in_array($value->name->identifier, $keys, true)) {
				DuplicateSubsetValue::ofEnumeration(
					$name->identifier,
					$value->name
				);
			}
			$keys[] = $value->name->identifier;
		}
		return new EnumerationType($name, array_combine($keys, $values));
	}

	public function alias(TypeNameIdentifier $name, Type $aliasedType): AliasType {
		return new AliasType($name, $aliasedType);
	}

	public function data(TypeNameIdentifier $name, Type $valueType): DataType {
		return new DataType($name, $valueType);
	}

	public function open(TypeNameIdentifier $name, Type $valueType): OpenType {
		return new OpenType($name, $valueType);
	}

	public function sealed(TypeNameIdentifier $name, Type $valueType): SealedType {
		return new SealedType($name, $valueType);
	}
}