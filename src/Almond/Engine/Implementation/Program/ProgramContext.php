<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionValueFactory as FunctionValueFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunctionFactory as UserlandFunctionFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandMethodFactory as UserlandMethodFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext as MethodContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NativeMethodRegistry as NativeMethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodBuilder as UserlandMethodBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeFinder;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeBuilder as UserlandTypeBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeFactory as UserlandTypeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeStorage as UserlandTypeStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory as ValidationFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory as VariableScopeFactoryInterface;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Code\Function\FunctionContextFiller;
use Walnut\Lang\Almond\Engine\Implementation\Code\Function\FunctionValueFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Function\UserlandFunctionFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\MethodFinder;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\MethodRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native\NamespaceConfigMap;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native\NativeMethodLoader;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native\NativeMethodRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Userland\UserlandMethodBuilder;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Userland\UserlandMethodFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Userland\UserlandMethodStorage;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\UserlandMethodValidator;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\NativeCodeTypeMapper;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Userland\UserlandTypeBuilder;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Userland\UserlandTypeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Userland\UserlandTypeStorage;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Userland\UserlandTypeValidator;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Common\BytesEscapeCharHandler;
use Walnut\Lang\Almond\Engine\Implementation\Common\StringEscapeCharHandler;
use Walnut\Lang\Almond\Engine\Implementation\Feature\DependencyContainer\DependencyContainer;
use Walnut\Lang\Almond\Engine\Implementation\Feature\DependencyContainer\DependencyContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Program\Execution\ExecutionContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Implementation\Program\VariableScope\VariableScopeFactory;


final readonly class ProgramContext implements ProgramContextInterface, TypeFinder {
	public UserlandTypeRegistry $userlandTypeRegistry;
	public UserlandTypeStorageInterface $userlandTypeStorage;
	public UserlandTypeFactoryInterface $userlandTypeFactory;
	public UserlandTypeBuilderInterface $userlandTypeBuilder;

	public UserlandFunctionFactoryInterface $userlandFunctionFactory;

	public NativeMethodRegistryInterface $nativeMethodRegistry;
	public UserlandMethodRegistry $userlandMethodRegistry;
	public UserlandMethodStorageInterface $userlandMethodStorage;
	public UserlandMethodFactoryInterface $userlandMethodFactory;
	public UserlandMethodBuilderInterface $userlandMethodBuilder;

	public VariableScopeFactoryInterface $variableScopeFactory;

	public ValidationFactoryInterface $validationFactory;
	public FunctionValueFactoryInterface $functionValueFactory;


	public TypeRegistryInterface $typeRegistry;
	public ValueRegistryInterface $valueRegistry;
	public DependencyContainerInterface $dependencyContainer;
	public MethodContextInterface $methodContext;

	public ExpressionRegistryInterface $expressionRegistry;

	public ProgramValidator $programValidator;

	public function __construct(array $nativeExtensionNamespaces = []) {
		// Storage + registry pairs:
		$this->userlandTypeRegistry = $this->userlandTypeStorage = new UserlandTypeStorage();
		$this->userlandMethodRegistry = $this->userlandMethodStorage = new UserlandMethodStorage();

		// Factories:
		$this->variableScopeFactory = new VariableScopeFactory();

		// The type registry is necessary for several factories, so it comes next:
		$stringEscapeCharHandler = new StringEscapeCharHandler();
		$bytesEscapeCharHandler = new BytesEscapeCharHandler();

		$this->validationFactory = new ValidationFactory($this);

		$this->nativeMethodRegistry = new NativeMethodRegistry(
			new NativeCodeTypeMapper(),
			new NativeMethodLoader(
				$this,
				new NamespaceConfigMap(
					$nativeExtensionNamespaces,
					'Walnut\Lang\Almond\Engine\NativeCode'
				)
			)
		);

		$this->methodContext = new MethodContext(
			$this,
			$this->validationFactory,
			new MethodFinder(
				new MethodRegistry(
					$this,
					$this->nativeMethodRegistry,
				)
			),
		);

		$this->typeRegistry = new TypeRegistry(
			$this->methodContext,
			$this->userlandTypeRegistry,
			$stringEscapeCharHandler
		);
		$this->valueRegistry = new ValueRegistry(
			$this->typeRegistry,
			$stringEscapeCharHandler,
			$bytesEscapeCharHandler
		);

		$this->dependencyContainer = new DependencyContainer(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodContext
		);

		// Factories continued:
		$this->userlandFunctionFactory = new UserlandFunctionFactory(
			$this->validationFactory,
			new ExecutionContextFactory(
				$this->typeRegistry->null->value
			),
			new FunctionContextFiller(
				$this->typeRegistry,
				$this->valueRegistry,
			),
			$this->dependencyContainer,
		);
		$this->functionValueFactory = new FunctionValueFactory(
			$this->typeRegistry,
			$this->userlandFunctionFactory,
			$this->variableScopeFactory,
		);

		$this->userlandTypeFactory = new UserlandTypeFactory(
			$this->variableScopeFactory
		);
		$this->userlandMethodFactory = new UserlandMethodFactory(
			$this->typeRegistry,
			$this->userlandFunctionFactory,
			$this->variableScopeFactory,
		);



		$this->expressionRegistry = new ExpressionRegistry(
			 $this->typeRegistry,
			 $this->valueRegistry,
			 $this->validationFactory,
			 $this->methodContext,
		 );


		$this->userlandTypeBuilder = new UserlandTypeBuilder(
			$this->userlandTypeFactory,
			$this->userlandTypeRegistry,
		);
		$this->userlandMethodBuilder = new UserlandMethodBuilder(
			$this->userlandMethodFactory,
			$this->userlandMethodStorage,
		);

		$this->programValidator = new ProgramValidator(
			new UserlandTypeValidator(
				$this->userlandTypeRegistry,
				$this->validationFactory,
			),
			new UserlandMethodValidator(
				new DependencyContextFactory(
					$this->dependencyContainer
				),
				$this->userlandMethodStorage,
				$this->validationFactory,
				$this->variableScopeFactory,
				$this->typeRegistry,
				$this->nativeMethodRegistry
			)
		);
	}

	public function validateAndBuildProgram(): Program|ValidationResult {
		$validationResult = $this->programValidator->validateProgram();
		if ($validationResult->hasErrors()) {
			return $validationResult;
		}
		return new Program(
			$this->typeRegistry,
			$this->dependencyContainer,
		);
	}

	public function typeByName(TypeName $typeName): Type {
		return $this->typeRegistry->typeByName($typeName);
	}
}