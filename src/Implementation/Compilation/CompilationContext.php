<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Compilation\CompilationContext as CompilationContextInterface;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder as ScopeBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder as TypeRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry as ProgramRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Implementation\Function\CustomMethodAnalyser;
use Walnut\Lang\Implementation\Program\Program;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;

final readonly class CompilationContext implements CompilationContextInterface {
	public ProgramRegistryInterface $programRegistry;

	public function __construct(
		public CustomMethodRegistryBuilderInterface      $customMethodRegistryBuilder,
		public CustomMethodRegistry                      $customMethodRegistry,
		public TypeRegistryBuilderInterface              $typeRegistryBuilder,
		public TypeRegistry                              $typeRegistry,
		public ValueRegistryInterface                    $valueRegistry,
		public ExpressionRegistryInterface               $expressionRegistry,
		public MethodRegistry                            $methodRegistry,
		public ScopeBuilderInterface                     $globalScopeBuilder,
	) {
		$this->programRegistry = new ProgramRegistry(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodRegistry,
			$this->globalScopeBuilder,
			$this->expressionRegistry,
		);
	}

	/** @throws AnalyserException */
	public function analyseAndBuildProgram(): Program {
		$customMethodAnalyser = new CustomMethodAnalyser(
			$this->programRegistry,
		);
		$analyseErrors = $customMethodAnalyser->analyse($this->customMethodRegistry);
		if (count($analyseErrors) > 0) {
			throw new AnalyserException(implode("\n", $analyseErrors));
		}
		return new Program(
			$this->programRegistry,
		);
	}
}