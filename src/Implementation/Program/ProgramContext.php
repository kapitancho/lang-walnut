<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder as CustomMethodRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder as TypeRegistryBuilderInterface;
use Walnut\Lang\Blueprint\Program\ProgramAnalyserException;
use Walnut\Lang\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
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
		public TypeRegistry                              $typeRegistry,
		public TypeRegistryBuilderInterface              $typeRegistryBuilder,
		public ValueRegistryInterface                    $valueRegistry,
		public ExpressionRegistryInterface               $expressionRegistry,
		public MethodFinder                              $methodRegistry,
		public MethodAnalyser                            $methodAnalyser,
		private VariableValueScope                       $variableValueScope,
		private NativeCodeTypeMapper                     $nativeCodeTypeMapper,
	) {
		$this->programRegistry = new ProgramRegistry(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodRegistry,
			$this->methodAnalyser,
			$this->variableValueScope,
		);
	}

	/** @throws ProgramAnalyserException */
	public function analyseAndBuildProgram(): Program {
		$customMethodAnalyser = new CustomMethodAnalyser(
			$this->programRegistry,
			$this->nativeCodeTypeMapper
		);
		$analyseErrors = $customMethodAnalyser->analyse($this->customMethodRegistry);
		if (count($analyseErrors) > 0) {
			throw new ProgramAnalyserException(... $analyseErrors);
		}
		return new Program(
			$this->programRegistry,
		);
	}
}