<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Closure;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionUnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionValueFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod as NativeMethodInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
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
 * @template TTargetType of Type
 * @template TParameterType of Type
 * @template TTargetValue of Value
 * @template TParameterValue of Value
 */
abstract readonly class NativeMethod implements NativeMethodInterface {
	use BaseType;

	public function __construct(
		protected ValidationFactory $validationFactory,
		protected TypeRegistry $typeRegistry,
		protected ValueRegistry $valueRegistry,
		protected MethodContext $methodContext,
		protected VariableScopeFactory $variableScopeFactory,
		protected DependencyContainer $dependencyContainer,
		protected ExpressionRegistry $expressionRegistry,
		protected FunctionValueFactory $functionValueFactory,
	) {}

	public function validate(
		Type $targetType,
		Type $parameterType,
		mixed $origin
	): ValidationSuccess|ValidationFailure {
		$validator = $this->getValidator();

		$baseTargetType = $this->toBaseType($targetType);
		$baseParameterType = $this->toBaseType($parameterType);

		$validatedTargetType = $this->isTargetTypeValid($baseTargetType, $validator, $origin);
		if ($validatedTargetType instanceof ValidationFailure) {
			return $validatedTargetType;
		}
		if (!$validatedTargetType) {
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				$origin
			);
		}
		$validatedParameterType = $this->isParameterTypeValid($baseParameterType, $validator);
		if (!$validatedParameterType) {
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		}
		$result = $validator(
			is_bool($validatedTargetType) ? $baseTargetType : $validatedTargetType,
			is_bool($validatedParameterType) ? $baseParameterType : $validatedParameterType,
			$origin
		);
		return $result instanceof Type ?
			$this->validationFactory->validationSuccess($result) : $result;
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type|ValidationFailure {
		return $this->matchesCallableParameter($validator, $targetType, 0);
	}

	protected function isParameterTypeValid(Type $parameterType, callable $validator): bool|Type {
		return $this->matchesCallableParameter($validator, $parameterType, 1);
	}

	/** @return callable(TTargetType, TParameterType, Expression|null): (Type|ValidationFailure) */
	abstract protected function getValidator(): callable;

	public function execute(Value $target, Value $parameter): Value {
		$executor = $this->getExecutor();
		if (!$this->isTargetValueValid($target, $executor)) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid target value");
			// @codeCoverageIgnoreEnd
		}
		if (!$this->isParameterValueValid($parameter, $executor)) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		return $executor($target, $parameter);
	}

	protected function isTargetValueValid(Value $target, callable $executor): bool {
		return $this->matchesCallableParameter($executor, $target, 0);
	}

	protected function isParameterValueValid(Value $parameter, callable $executor): bool {
		return $this->matchesCallableParameter($executor, $parameter, 1);
	}

	/** @return callable(TTargetValue, TParameterValue): Value */
	abstract protected function getExecutor(): callable;

	private function matchesCallableParameter(callable $callable, object $value, int $parameterIndex): bool|Type {
		$reflection = new ReflectionFunction(Closure::fromCallable($callable));
		$type = $reflection->getParameters()[$parameterIndex]->getType();

		$q = function(object $value, string $typeName): bool|Type {
			if ($value instanceof $typeName) {
				return true;
			}
			if ($value instanceof IntersectionType) {
				foreach($value->types as $it) {
					if ($it instanceof $typeName) {
						return $it;
					}
					if ($it instanceof AliasType && $it->aliasedType instanceof $typeName) {
						return $it->aliasedType;
					}
				}
			}
			return false;
		};

		if ($type instanceof ReflectionNamedType) {
			$typeName = $type->getName();
			return $q($value, $typeName);
		}
		if ($type instanceof ReflectionUnionType) {
			foreach ($type->getTypes() as $t) {
				if ($t instanceof ReflectionNamedType) {
					$typeName = $t->getName();
					if ($q($value, $typeName)) {
						return true;
					}
				}
			}
			return false;
		}
		return true;
	}

}