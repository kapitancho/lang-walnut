<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry as ProgramRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Program\DependencyContainer\DependencyContainer;

final class ProgramRegistry implements ProgramRegistryInterface {
	private readonly AnalyserContextInterface $analyserContextInstance;
	private readonly ExecutionContextInterface $executionContextInstance;
	private readonly DependencyContainerInterface $dependencyContainerInstance;

	public function __construct(
		public readonly TypeRegistry                 $typeRegistry,
		public readonly ValueRegistry                $valueRegistry,
		public readonly MethodFinder                 $methodFinder,
		private readonly VariableValueScopeInterface $variableValueScope,
		private readonly ExpressionRegistryInterface $expressionRegistry,
	) {}

	public AnalyserContextInterface $analyserContext {
		get {
			return $this->analyserContextInstance ??= new AnalyserContext(
				$this,
				$this->variableValueScope,
			);
		}
	}
	public ExecutionContextInterface $executionContext {
		get {
			return $this->executionContextInstance ??= new ExecutionContext(
				$this,
				$this->variableValueScope,
			);
		}
	}

	public DependencyContainerInterface $dependencyContainer {
		get {
			return $this->dependencyContainerInstance ??= new DependencyContainer(
				$this,
				$this->executionContext,
				$this->methodFinder,
				$this->expressionRegistry
			);
		}
	}
}