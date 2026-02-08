<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

interface UserlandMethod extends Method {
	public TypeName $targetType { get; }
	public MethodName $methodName { get; }
	public Type $parameterType { get; }
	public Type $returnType { get; }

	public function validateFunction(): ValidationSuccess|ValidationFailure;

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;
}