<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionContextFiller;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction as UserlandFunctionInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunctionFactory as UserlandFunctionFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContextFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;

final readonly class UserlandFunctionFactory implements UserlandFunctionFactoryInterface {
	public function __construct(
		private ValidationFactory        $validationFactory,
		private TypeRegistry             $typeRegistry,
		private ValueRegistry            $valueRegistry,
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
			$this->typeRegistry,
			$this->valueRegistry,
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