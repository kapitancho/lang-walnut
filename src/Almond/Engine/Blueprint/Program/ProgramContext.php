<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\FunctionValueFactory as FunctionValueFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandFunctionFactory as UserlandFunctionFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandMethodFactory as UserlandMethodFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Method\MethodContext as MethodContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodBuilder as UserlandMethodBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeBuilder as UserlandTypeBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeFactory as UserlandTypeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeStorage as UserlandTypeStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory as ValidationFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScopeFactory as VariableScopeFactoryInterface;
use Walnut\Lang\Almond\Engine\Implementation\Program\ProgramValidator;

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