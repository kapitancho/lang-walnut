<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program;

use Walnut\Lang\Almond\AST\Implementation\Parser\BytesEscapeCharHandler;
use Walnut\Lang\Almond\AST\Implementation\Parser\StringEscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\FunctionValueFactory as FunctionValueFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandFunctionFactory as UserlandFunctionFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandMethodFactory as UserlandMethodFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\MethodContext as MethodContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeFinder;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodBuilder as UserlandMethodBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeBuilder as UserlandTypeBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeFactory as UserlandTypeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeStorage as UserlandTypeStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory as ValidationFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScopeFactory as VariableScopeFactoryInterface;
use Walnut\Lang\Almond\Engine\Implementation\Abc\FunctionValueFactory;
use Walnut\Lang\Almond\Engine\Implementation\Dependency\DependencyContainer;
use Walnut\Lang\Almond\Engine\Implementation\Dependency\DependencyContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Execution\ExecutionContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Function\FunctionContextFiller;
use Walnut\Lang\Almond\Engine\Implementation\Function\UserlandFunctionFactory;
use Walnut\Lang\Almond\Engine\Implementation\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Implementation\Method\MethodFinder;
use Walnut\Lang\Almond\Engine\Implementation\Method\UserlandMethodValidator;
use Walnut\Lang\Almond\Engine\Implementation\Registry\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Registry\MethodRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Native\NamespaceConfigMap;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Native\NativeCodeTypeMapper;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Native\NativeMethodLoader;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Native\NativeMethodRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Userland\UserlandMethodBuilder;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Userland\UserlandMethodFactory;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Userland\UserlandMethodStorage;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Userland\UserlandTypeBuilder;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Userland\UserlandTypeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Userland\UserlandTypeStorage;
use Walnut\Lang\Almond\Engine\Implementation\Registry\Userland\UserlandTypeValidator;
use Walnut\Lang\Almond\Engine\Implementation\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Implementation\VariableScope\VariableScopeFactory;


final readonly class ProgramContext implements ProgramContextInterface, TypeFinder {
	public UserlandTypeRegistry $userlandTypeRegistry;
	public UserlandTypeStorageInterface $userlandTypeStorage;
	public UserlandTypeFactoryInterface $userlandTypeFactory;
	public UserlandTypeBuilderInterface $userlandTypeBuilder;

	public UserlandFunctionFactoryInterface $userlandFunctionFactory;

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

		$this->methodContext = new MethodContext(
			$this,
			$this->validationFactory,
			new MethodFinder(
				new MethodRegistry(
					$this,
					new NativeMethodRegistry(
						new NativeCodeTypeMapper(),
						new NativeMethodLoader(
							$this,
							new NamespaceConfigMap(
								$nativeExtensionNamespaces,
								'Walnut\Lang\Almond\Engine\NativeCode'
							)
						)
					),
					$this->userlandMethodStorage
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
				$this->variableScopeFactory
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

	public function typeByName(TypeName $typeName): Type|UnknownType {
		return $this->typeRegistry->typeByName($typeName);
	}
}