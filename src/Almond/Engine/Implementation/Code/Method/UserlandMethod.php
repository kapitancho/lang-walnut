<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod as CustomMethodInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;

final readonly class UserlandMethod implements CustomMethodInterface {

	public function __construct(
		private UserlandFunction $function,

		private VariableScopeFactory $variableScopeFactory,

		public TypeName $targetType,
		public MethodName $methodName,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		return $this->function->validate($targetType, $parameterType);
	}

	public function validateFunction(): ValidationSuccess|ValidationFailure {
		return $this->function->validateInVariableScope($this->variableScopeFactory->emptyVariableScope);
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->function->validateDependencies($dependencyContext);
	}

	public function execute(Value $target, Value $parameter): Value {
		return $this->function->execute($this->variableScopeFactory->emptyVariableValueScope,
			$target, $parameter);
	}
}