<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry as ProgramRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Program\Builder\ScopeBuilder;
use Walnut\Lang\Implementation\Program\DependencyContainer\DependencyContainer;

final class ProgramRegistry implements ProgramRegistryInterface {
	private readonly AnalyserContextInterface $analyserContextInstance;
	private readonly ExecutionContextInterface $executionContextInstance;
	private readonly DependencyContainerInterface $dependencyContainerInstance;
	private readonly VariableValueScope $variableValueScopeInstance;

	public function __construct(
		public readonly TypeRegistry                 $typeRegistry,
		public readonly ValueRegistry                $valueRegistry,
		public readonly MethodRegistry               $methodRegistry,
		private readonly ScopeBuilder                $globalScopeBuilder,
		private readonly ExpressionRegistryInterface $expressionRegistry,
	) {}

	public VariableValueScope $globalScope {
		get {
			return $this->variableValueScopeInstance ??= $this->globalScopeBuilder->build();
		}
	}

	public AnalyserContextInterface $analyserContext {
		get {
			return $this->analyserContextInstance ??= new AnalyserContext(
				$this,
				$this->globalScope,
			);
		}
	}
	public ExecutionContextInterface $executionContext {
		get {
			return $this->executionContextInstance ??= new ExecutionContext(
				$this,
				$this->globalScope,
			);
		}
	}

	public DependencyContainerInterface $dependencyContainer {
		get {
			return $this->dependencyContainerInstance ??= new DependencyContainer(
				$this,
				$this->executionContext,
				$this->methodRegistry,
				$this->expressionRegistry
			);
		}
	}
}