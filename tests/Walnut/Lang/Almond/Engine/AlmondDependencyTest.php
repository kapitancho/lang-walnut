<?php

namespace Walnut\Lang\Test\Almond\Engine;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Implementation\Dependency\DependencyContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Execution\ExecutionContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Implementation\Function\FunctionContextFiller;
use Walnut\Lang\Almond\Engine\Implementation\Function\UserlandFunctionFactory;
use Walnut\Lang\Almond\Engine\Implementation\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Implementation\Method\MethodFinder;
use Walnut\Lang\Almond\Engine\Implementation\Method\UserlandMethodValidator;
use Walnut\Lang\Almond\Engine\Implementation\Program\ProgramValidator;
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
use Walnut\Lang\Almond\Engine\Implementation\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\NameAndType;
use Walnut\Lang\Almond\Engine\Implementation\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Implementation\VariableScope\VariableScopeFactory;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Test\Almond\AlmondBaseTestHelper;

final class AlmondDependencyTest extends AlmondBaseTestHelper {

	public function testManualDependencyCreation(): void {
		$dependencyContainer = $this->createMock(DependencyContainer::class);
		$dependencyContextFactory = new DependencyContextFactory($dependencyContainer);

		$userlandTypeRegistry = new UserlandTypeStorage();
		$typeRefFactory = new TypeRefFactory(
			$userlandTypeRegistry
		);
		$typeRegistry = new TypeRegistry(
			$userlandTypeRegistry,
			$typeRefFactory
		);
		$userlandTypeBuilder = new UserlandTypeBuilder(
			new UserlandTypeFactory($typeRefFactory),
			$userlandTypeRegistry,
		);
		$validationFactory = new ValidationFactory(
			$typeRegistry->null,
			$typeRegistry->null
		);
		$userlandMethodValidator = new UserlandMethodValidator(
			$dependencyContextFactory,
			$userlandMethodStorage = new UserlandMethodStorage(),
			$validationFactory
		);
		$functionContextFiller = new FunctionContextFiller();
		$executionContextFactory = new ExecutionContextFactory(
			$typeRegistry->null->value
		);
		$userlandFunctionFactory = new UserlandFunctionFactory(
			$validationFactory,
			$executionContextFactory,
			$functionContextFiller,
			$dependencyContainer,
		);
		$variableScopeFactory = new VariableScopeFactory();
		$valueRegistry = new ValueRegistry(
			$typeRegistry,
			$typeRefFactory,
		);
		$methodFinder = new MethodFinder(
			new MethodRegistry(
				new NativeMethodRegistry(
					new NativeCodeTypeMapper(),
					new NativeMethodLoader(
						new NamespaceConfigMap(
							[],
							'Walnut\Lang\Almond\Engine\NativeCode'
						)
					)
				),
				$userlandMethodStorage
			)

		);
		$methodContext = new MethodContext($validationFactory, $methodFinder);
		$expressionRegistry = new ExpressionRegistry(
			$typeRegistry,
			$valueRegistry,
			$validationFactory,
			$methodContext,
		);
		$userlandMethodBuilder = new UserlandMethodBuilder(
			new UserlandMethodFactory(
				$typeRegistry,
				$userlandFunctionFactory,
				$variableScopeFactory,
			),
			$userlandMethodStorage
		);
		$programValidator = new ProgramValidator(
			new UserlandTypeValidator(
				$userlandTypeRegistry,
				$validationFactory,
			),
			$userlandMethodValidator,
		);



		$customMethod = $userlandMethodBuilder->addMethod(
			new TypeName('Null'),
			new MethodName('hello'),
			new NameAndType($typeRegistry->null, new VariableName('param')),
			new NameAndType($typeRegistry->null, new VariableName('dep')),
			$typeRegistry->any,
			$b = new FunctionBody(
				$typeRegistry,
				$validationFactory,
				$expressionRegistry->tuple([
					$expressionRegistry->sequence([
						$expressionRegistry->variableAssignment(
							new VariableName('x'),
							$expressionRegistry->constant(
								$valueRegistry->type(
									$typeRegistry->null
								)
							)
						),
						$expressionRegistry->constant(
							$valueRegistry->null,
						),
					]),
					$expressionRegistry->variableName(new VariableName('x')),
				])
			)
		);

		$dependencyContainer->method('valueForType')->willReturn(
			$valueRegistry->null
		);

		$validationResult = $programValidator->validateProgram();
		//$this->assertEquals('[{x = null; type{Null}}, x]', (string)$b);
		//var_dump($validationResult);
		$this->assertInstanceOf(Program::class, $validationResult);
		//$this->assertInstanceOf(ValidationFailure::class, $validationResult);

		$this->assertEquals("[null, type{Null}]", (string)$customMethod->execute(
			$valueRegistry->null,
			$valueRegistry->null,
		));
	}

}