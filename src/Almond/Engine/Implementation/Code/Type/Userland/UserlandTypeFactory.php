<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeFactory as UserlandTypeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\SealedType;

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