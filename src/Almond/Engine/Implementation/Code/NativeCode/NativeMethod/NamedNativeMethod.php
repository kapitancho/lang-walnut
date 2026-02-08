<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Closure;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionUnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod as NativeMethodInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

/**
 * @template TTargetType of NamedType
 * @template TParameterType of Type
 * @template TTargetValue of Value
 * @template TParameterValue of Value
 * @extends NativeMethod<TTargetType, TParameterType, TTargetValue, TParameterValue>
 */
abstract readonly class NamedNativeMethod extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, Expression|null $origin): bool {
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		/** @var NamedType $targetType */
		return $this->isNamedTypeValid($targetType, $origin);
	}

	protected function isNamedTypeValid(NamedType $namedType, Expression|null $origin): bool {
		return true;
	}

	protected function isTargetValueValid(Value $target, callable $executor): bool {
		if (!parent::isTargetValueValid($target, $executor)) {
			return false;
		}
		/** @var NamedType $targetType */
		$targetType = $target->type;
		return $this->isNamedTypeValid($targetType, null);
	}

}