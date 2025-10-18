<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder as TypeRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry as ProgramRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Implementation\Function\CustomMethodAnalyser;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;

final readonly class ProgramContext implements ProgramContextInterface {
	public ProgramRegistryInterface $programRegistry;

	public function __construct(
		public CustomMethodRegistryBuilderInterface      $customMethodRegistryBuilder,
		public CustomMethodRegistry                      $customMethodRegistry,
		public TypeRegistryBuilderInterface              $typeRegistryBuilder,
		public TypeRegistry                              $typeRegistry,
		public ValueRegistryInterface                    $valueRegistry,
		public ExpressionRegistryInterface               $expressionRegistry,
		public MethodFinder                              $methodRegistry,
		private VariableValueScope                        $variableValueScope,
		private NativeCodeTypeMapper                     $nativeCodeTypeMapper,
	) {
		$this->programRegistry = new ProgramRegistry(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodRegistry,
			$this->variableValueScope,
		);
	}

	/** @throws AnalyserException */
	public function analyseAndBuildProgram(): Program {
		$customMethodAnalyser = new CustomMethodAnalyser(
			$this->programRegistry,
			$this->nativeCodeTypeMapper
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