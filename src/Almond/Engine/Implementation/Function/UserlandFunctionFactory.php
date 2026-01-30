<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandFunctionFactory as UserlandFunctionFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContextFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionContextFiller;
use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction as UserlandFunctionInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;

final readonly class UserlandFunctionFactory implements UserlandFunctionFactoryInterface {
	public function __construct(
		private ValidationFactory        $validationFactory,
		private ExecutionContextFactory  $executionContextFactory,

		private FunctionContextFiller    $functionContextFiller,
		private DependencyContainer 	 $dependencyContainer,
	) {}

	public function create(
		NameAndType $target,
		NameAndType $parameter,
		NameAndType $dependency,
		Type $returnType,
		FunctionBody $functionBody
	): UserlandFunctionInterface {
		return new UserlandFunction(
			$this->validationFactory,
			$this->executionContextFactory,
			$this->functionContextFiller,
			$this->dependencyContainer,

			$target,
			$parameter,
			$dependency,
			$returnType,
			$functionBody
		);
	}
}