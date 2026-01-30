<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;

interface UserlandMethod extends Method {
	public TypeName $targetType { get; }
	public MethodName $methodName { get; }

	public function validateFunction(): ValidationSuccess|ValidationFailure;

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;
}