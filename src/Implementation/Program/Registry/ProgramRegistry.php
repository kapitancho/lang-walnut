<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext as AnalyserContextInterface;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer as DependencyContainerInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext as MethodContextInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry as ProgramRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\NativeCode\ValueConstructor;
use Walnut\Lang\Implementation\Program\DependencyContainer\DependencyContainer;

final class ProgramRegistry implements ProgramRegistryInterface {
	private readonly AnalyserContextInterface $analyserContextInstance;
	private readonly ExecutionContextInterface $executionContextInstance;
	private readonly DependencyContainerInterface $dependencyContainerInstance;
	private readonly MethodContextInterface $methodContextInstance;

	public function __construct(
		public readonly TypeRegistry                 $typeRegistry,
		public readonly ValueRegistry                $valueRegistry,
		private readonly MethodFinder                $methodFinder,
		private readonly MethodAnalyser              $methodAnalyser,
		private readonly VariableValueScopeInterface $variableValueScope,
	) {}

	public MethodContextInterface $methodContext {
		get {
			return $this->methodContextInstance ??= new MethodContext(
				$this,
				$this->methodFinder,
				$this->methodAnalyser
			);
		}
	}

	public AnalyserContextInterface $analyserContext {
		get {
			return $this->analyserContextInstance ??= new AnalyserContext(
				$this->typeRegistry,
				$this->methodContext,
				$this->variableValueScope,
			);
		}
	}

	public ExecutionContextInterface $executionContext {
		get {
			return $this->executionContextInstance ??= new ExecutionContext(
				$this->dependencyContainer,
				$this->valueRegistry,
				$this->typeRegistry,
				$this->methodContext,
				$this->variableValueScope,
			);
		}
	}

	public DependencyContainerInterface $dependencyContainer {
		get {
			return $this->dependencyContainerInstance ??= new DependencyContainer(
				$this,
				$this->valueRegistry,
				$this->methodFinder,
				new ValueConstructor()
			);
		}
	}
}