<?php

namespace Walnut\Lang\Blueprint\Program;

use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

interface ProgramContext {
	public TypeRegistryBuilder                 $typeRegistryBuilder { get; }
	public TypeRegistry                        $typeRegistry { get; }
	public ValueRegistry                       $valueRegistry { get; }
	public ExpressionRegistry                  $expressionRegistry { get; }
	public CustomMethodRegistryBuilder         $customMethodRegistryBuilder { get; }
	public CustomMethodRegistry                $customMethodRegistry { get; }
	public MethodRegistry                      $methodRegistry { get; }

	public ProgramRegistry                     $programRegistry { get; }

	/** @throws ProgramAnalyserException */
	public function analyseAndBuildProgram(): Program;
}