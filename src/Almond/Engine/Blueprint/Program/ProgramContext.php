<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionValueFactory as FunctionValueFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunctionFactory as UserlandFunctionFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandMethodFactory as UserlandMethodFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext as MethodContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodBuilder as UserlandMethodBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeBuilder as UserlandTypeBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeFactory as UserlandTypeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeStorage as UserlandTypeStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory as ValidationFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory as VariableScopeFactoryInterface;

interface ProgramContext {
	public UserlandTypeRegistry $userlandTypeRegistry { get; }
	public UserlandTypeStorageInterface $userlandTypeStorage { get; }
	public UserlandTypeFactoryInterface $userlandTypeFactory { get; }
	public UserlandTypeBuilderInterface $userlandTypeBuilder { get; }

	public UserlandFunctionFactoryInterface $userlandFunctionFactory { get; }

	public UserlandMethodRegistry $userlandMethodRegistry { get; }
	public UserlandMethodStorageInterface $userlandMethodStorage { get; }
	public UserlandMethodFactoryInterface $userlandMethodFactory { get; }
	public UserlandMethodBuilderInterface $userlandMethodBuilder { get; }

	public VariableScopeFactoryInterface $variableScopeFactory { get; }

	public ValidationFactoryInterface $validationFactory { get; }
	public FunctionValueFactoryInterface $functionValueFactory { get; }


	public TypeRegistryInterface $typeRegistry { get; }
	public ValueRegistryInterface $valueRegistry { get; }
	public DependencyContainerInterface $dependencyContainer { get; }
	public MethodContextInterface $methodContext { get; }

	public ExpressionRegistryInterface $expressionRegistry { get; }

	public ProgramValidator $programValidator { get; }

	public function validateAndBuildProgram(): Program|ValidationResult;
}