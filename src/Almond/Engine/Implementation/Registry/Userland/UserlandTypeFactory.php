<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeFactory as UserlandTypeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScopeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Type\AtomType;
use Walnut\Lang\Almond\Engine\Implementation\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\AliasType;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\DataType;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\OpenType;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\SealedType;

final readonly class UserlandTypeFactory implements UserlandTypeFactoryInterface {

	public function __construct(
		private VariableScopeFactory $variableScopeFactory
	) {}

	public function atom(TypeName $name): AtomType {
		return new AtomType($name);
	}

	/** @param list<EnumerationValueName> $values */
	public function enumeration(TypeName $name, array $values): EnumerationTypeInterface {
		return new EnumerationType($name, $values);
	}

	public function alias(TypeName $name, Type $aliasedType): AliasType {
		return new AliasType($name, $aliasedType);
	}

	public function data(TypeName $name, Type $valueType): DataType {
		return new DataType($name, $valueType);
	}

	public function open(TypeName $name, Type $valueType, UserlandFunction|null $validator): OpenType {
		return new OpenType($this->variableScopeFactory, $name, $valueType, $validator);
	}

	public function sealed(TypeName $name, Type $valueType, UserlandFunction|null $validator): SealedType {
		return new SealedType($this->variableScopeFactory, $name, $valueType, $validator);
	}
}