<?php

namespace Walnut\Lang\Implementation\Program\Factory;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\Factory\ProgramFactory as ProgramFactoryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Compilation\CodeBuilder;
use Walnut\Lang\Implementation\Function\MethodExecutionContext;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\ProgramBuilder;
use Walnut\Lang\Implementation\Program\Builder\ScopeBuilder;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Implementation\Program\GlobalContext;
use Walnut\Lang\Implementation\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final readonly class ProgramFactory implements DependencyContainerInterface, ProgramFactoryInterface {

	private TypeRegistryBuilder $typeRegistryBuilder;
	private ValueRegistry $valueRegistry;
	private ExpressionRegistry $expressionRegistry;
	private MethodRegistry $methodRegistry;
	private DependencyContainer $dependencyContainer;
	private CustomMethodRegistryBuilder $customMethodRegistryBuilder;
	private ScopeBuilder $globalScopeBuilder;
	private AnalyserContext&ExecutionContext $globalContext;

	private ProgramBuilder $programBuilder;
	private ProgramRegistry $programRegistry;

	public function __construct() {
		$this->typeRegistryBuilder = new TypeRegistryBuilder;
		$this->valueRegistry = new ValueRegistry(
			$this->typeRegistryBuilder,
			$this
		);
		$this->globalScopeBuilder = new ScopeBuilder(VariableValueScope::empty());
		$this->globalContext = new GlobalContext($this->globalScopeBuilder);
		$methodExecutionContext = new MethodExecutionContext(
			$this->typeRegistryBuilder,
			$this->valueRegistry,
			$this->globalContext
		);
		$this->customMethodRegistryBuilder = new CustomMethodRegistryBuilder(
			$methodExecutionContext,
			$this
		);
		$this->methodRegistry = new MainMethodRegistry(
			$methodExecutionContext,
			new NativeCodeTypeMapper(),
			$this->customMethodRegistryBuilder,
			$this,
			[
				'Walnut\\Lang\\NativeCode'
			]
		);
		$this->expressionRegistry = new ExpressionRegistry(
			$this->typeRegistryBuilder,
			$this->valueRegistry,
			$this->methodRegistry
		);
		$this->dependencyContainer = new DependencyContainer(
			$this->valueRegistry,
			$this->globalContext,
			$this->methodRegistry,
			$this->expressionRegistry
		);
	}

	public function valueByType(Type $type): Value|DependencyError {
		return $this->dependencyContainer->valueByType($type);
	}

	public function codeBuilder(): CodeBuilder {
		return new CodeBuilder(
			$this->typeRegistryBuilder,
			$this->valueRegistry,
			$this->builder(),
			$this->expressionRegistry,
		);
	}

	public function builder(): ProgramBuilder {
		return $this->programBuilder ??= new ProgramBuilder(
			$this->typeRegistryBuilder,
			$this->expressionRegistry,
			$this->typeRegistryBuilder,
			$this->customMethodRegistryBuilder,
			$this->globalScopeBuilder,
			$this->globalContext
		);
	}

	public function registry(): ProgramRegistry {
		return $this->programRegistry ??= new ProgramRegistry(
			$this->typeRegistryBuilder,
			$this->valueRegistry,
			$this->expressionRegistry,
			$this->globalScopeBuilder,
			$this->customMethodRegistryBuilder
		);
	}

	public function addMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBody $functionBody
	): CustomMethod {
		return $this->customMethodRegistryBuilder->addMethod(
			$targetType,
			$methodName,
			$parameterType,
			$dependencyType,
			$returnType,
			$functionBody
		);
	}
}