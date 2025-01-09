<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Compilation\CodeBuilder as CodeBuilderInterface;
use Walnut\Lang\Blueprint\Compilation\CompilationContext as CompilationContextInterface;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder as ScopeBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder as TypeRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\ScopeBuilder;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final readonly class CompilationContext implements CompilationContextInterface {
	public TypeRegistryBuilderInterface              $typeRegistryBuilder;
	public TypeRegistry                              $typeRegistry;
	public ValueRegistryInterface                    $valueRegistry;
	public ExpressionRegistryInterface               $expressionRegistry;
	public CustomMethodRegistryBuilderInterface      $customMethodRegistryBuilder;
	public CustomMethodRegistry&MethodRegistry       $customMethodRegistry;
	public ScopeBuilderInterface                     $globalScopeBuilder;
	public CodeBuilderInterface                      $codeBuilder;
	public AnalyserContext&ExecutionContext          $globalContext;

	public function __construct() {
		$this->typeRegistry = $this->typeRegistryBuilder = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry);
		$this->expressionRegistry = new ExpressionRegistry();
		$this->customMethodRegistry = $this->customMethodRegistryBuilder = new CustomMethodRegistryBuilder(
			$this->typeRegistry
		);
		$this->codeBuilder = new CodeBuilder(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->expressionRegistry
		);
		$this->globalScopeBuilder = new ScopeBuilder(VariableValueScope::empty());
	}
}