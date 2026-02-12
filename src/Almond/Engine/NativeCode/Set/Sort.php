<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionValueFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\Composite\SortHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<SetType, Type, SetValue, Value> */
final readonly class Sort extends NativeMethod {

	private SortHelper $sortHelper;

	public function __construct(
		ValidationFactory $validationFactory,
		TypeRegistry $typeRegistry,
		ValueRegistry $valueRegistry,
		MethodContext $methodContext,
		VariableScopeFactory $variableScopeFactory,
		DependencyContainer $dependencyContainer,
		ExpressionRegistry $expressionRegistry,
		FunctionValueFactory $functionValueFactory,
	) {
		parent::__construct(
			$validationFactory,
			$typeRegistry,
			$valueRegistry,
			$methodContext,
			$variableScopeFactory,
			$dependencyContainer,
			$expressionRegistry,
			$functionValueFactory
		);
		$this->sortHelper = new SortHelper($validationFactory, $typeRegistry, $valueRegistry);
	}

	protected function getValidator(): callable {
		return fn(SetType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure =>
			$this->sortHelper->validate($targetType, $targetType, $parameterType, $origin);
	}

	protected function getExecutor(): callable {
		return fn(SetValue $target, Value $parameter): Value =>
			$this->sortHelper->execute(
				$target,
				$parameter,
				fn(array $values) => $this->valueRegistry->set(array_values($values))
			);
	}
}
