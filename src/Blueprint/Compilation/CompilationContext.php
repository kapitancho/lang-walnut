<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

interface CompilationContext {
	public TypeRegistryBuilder                 $typeRegistryBuilder { get; }
	public TypeRegistry                        $typeRegistry { get; }
	public ValueRegistry                       $valueRegistry { get; }
	public ExpressionRegistry                  $expressionRegistry { get; }
	public CustomMethodRegistryBuilder         $customMethodRegistryBuilder { get; }
	public CustomMethodRegistry&MethodRegistry $customMethodRegistry { get; }
	public ScopeBuilder                        $globalScopeBuilder { get; }
	public CodeBuilder                         $codeBuilder { get; }
	public AnalyserContext&ExecutionContext    $globalContext { get; }
}