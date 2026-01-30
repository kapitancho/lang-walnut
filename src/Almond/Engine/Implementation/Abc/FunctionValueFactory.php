<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Abc;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\FunctionValueFactory as FunctionValueFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType as NameAndTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandFunctionFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\FunctionValue as FunctionValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScopeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\NameAndType;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\FunctionValue;

final readonly class FunctionValueFactory implements FunctionValueFactoryInterface {

	public function __construct(
		private TypeRegistryInterface   $typeRegistry,
		private UserlandFunctionFactory $userlandFunctionFactory,
		private VariableScopeFactory    $variableScopeFactory,
	) {
	}

	public function function(
		NameAndTypeInterface $parameter, NameAndTypeInterface $dependency, Type $returnType, FunctionBody $body,
	): FunctionValueInterface {
		return new FunctionValue(
			$this->typeRegistry,
			$this->userlandFunctionFactory->create(
				new NameAndType($this->typeRegistry->nothing, null),
				$parameter,
				$dependency,
				$returnType,
				$body,
			),
			$this->variableScopeFactory->emptyVariableValueScope,
			null,
		);
	}

}