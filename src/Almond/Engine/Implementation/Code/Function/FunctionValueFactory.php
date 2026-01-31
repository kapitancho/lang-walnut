<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionValueFactory as FunctionValueFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunctionFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue as FunctionValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\FunctionValue;

final readonly class FunctionValueFactory implements FunctionValueFactoryInterface {

	public function __construct(
		private TypeRegistryInterface   $typeRegistry,
		private UserlandFunctionFactory $userlandFunctionFactory,
		private VariableScopeFactory    $variableScopeFactory,
	) {
	}

	public function function(
		NameAndType $parameter, NameAndType $dependency, Type $returnType, FunctionBody $functionBody,
	): FunctionValueInterface {
		return new FunctionValue(
			$this->typeRegistry,
			$this->userlandFunctionFactory->create(
				new NameAndType($this->typeRegistry->nothing, null),
				$parameter,
				$dependency,
				$returnType,
				$functionBody,
			),
			$this->variableScopeFactory->emptyVariableValueScope,
			null,
		);
	}

}