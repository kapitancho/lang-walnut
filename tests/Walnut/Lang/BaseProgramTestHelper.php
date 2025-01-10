<?php

namespace Walnut\Lang\Test;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\CompilationContext as CompilationContextInterface;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder as ScopeBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Program as ProgramInterface;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Implementation\Compilation\CompilationContextFactory;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\UnionType;

abstract class BaseProgramTestHelper extends TestCase {
	protected TypeRegistry $typeRegistry;
	protected ValueRegistry $valueRegistry;
	protected ExpressionRegistry $expressionRegistry;
	protected TypeRegistryBuilder $typeRegistryBuilder;
	protected CustomMethodRegistryBuilder $customMethodRegistryBuilder;
	protected ScopeBuilderInterface $globalScopeBuilder;
	protected ProgramRegistry $programRegistry;
	protected CompilationContextInterface $compilationContext;

	private function getCompilationContext(): CompilationContextInterface {
		return new CompilationContextFactory()->compilationContext;
	}

	protected function setUp(): void {
		parent::setUp();

		$this->compilationContext = $compilationContext = $this->getCompilationContext();
		$this->globalScopeBuilder = $compilationContext->globalScopeBuilder;
		$this->typeRegistry = $compilationContext->typeRegistry;
		$this->typeRegistryBuilder = $compilationContext->typeRegistryBuilder;
		$this->valueRegistry = $compilationContext->valueRegistry;
		$this->expressionRegistry = $compilationContext->expressionRegistry;
		$this->customMethodRegistryBuilder = $compilationContext->customMethodRegistryBuilder;
		$this->programRegistry = $compilationContext->programRegistry;

		$this->addCoreToContext();
		//$this->program = $compilationContext->analyseAndBuildProgram();
	}

	protected ProgramInterface $program {
		get => $this->compilationContext->analyseAndBuildProgram();
	}
	
	protected function addCoreToContext(): void {
		foreach(['NotANumber', 'MinusInfinity', 'PlusInfinity', 'DependencyContainer', 'Constructor'] as $atomType) {
			$this->typeRegistry->addAtom(
				new TypeNameIdentifier($atomType)
			);
		}
		$ef = fn() => $this->expressionRegistry->functionBody(
			$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('IndexOutOfRange'),
			$this->typeRegistry->record([
				'index' => $this->typeRegistry->integer()
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('CastNotAvailable'),
			$this->typeRegistry->record([
				'from' => $this->typeRegistry->type($this->typeRegistry->any),
				'to' => $this->typeRegistry->type($this->typeRegistry->any),
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('MapItemNotFound'),
			$this->typeRegistry->record([
				'key' => $this->typeRegistry->string()
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('InvalidJsonValue'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('InvalidJsonString'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->string()
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('UnknownEnumerationValue'),
			$this->typeRegistry->record([
				'enumeration' => $this->typeRegistry->type($this->typeRegistry->any),
				'value' => $this->typeRegistry->string(),
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('DependencyContainerError'),
			$this->typeRegistry->record([
				'targetType' => $this->typeRegistry->type($this->typeRegistry->any),
				'errorMessage' => $this->typeRegistry->string(),
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('HydrationError'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any,
				'hydrationPath' => $this->typeRegistry->string(),
				'errorMessage' => $this->typeRegistry->string(),
			]),
			$ef(),
			$this->typeRegistry->nothing
		);

		$j = $this->typeRegistry->proxyType(
			new TypeNameIdentifier('JsonValue')
		);

		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('JsonValue'),
			new UnionType(
				new UnionTypeNormalizer($this->typeRegistry),
				$this->typeRegistry->null,
				$this->typeRegistry->boolean,
				$this->typeRegistry->integer(),
				$this->typeRegistry->real(),
				$this->typeRegistry->string(),
				$this->typeRegistry->array($j),
				$this->typeRegistry->map($j),
				$this->typeRegistry->mutable($j)
			)
		);

		$this->typeRegistry->addSubtype(
			new TypeNameIdentifier('DatabaseConnection'),
			$this->typeRegistry->record([
				'dsn' => $this->typeRegistry->string()
			]),
			$ef(),
			null
		);
		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('DatabaseValue'),
			$this->typeRegistry->union([
				$this->typeRegistry->string(),
				$this->typeRegistry->integer(),
				$this->typeRegistry->boolean,
				$this->typeRegistry->null
			])
		);
		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('DatabaseQueryBoundParameters'),
			$this->typeRegistry->union([
				$this->typeRegistry->array(
					$this->typeRegistry->withName(
						new TypeNameIdentifier('DatabaseValue')
					)
				),
				$this->typeRegistry->map(
					$this->typeRegistry->withName(
						new TypeNameIdentifier('DatabaseValue')
					)
				)
			])
		);
		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('DatabaseQueryCommand'),
			$this->typeRegistry->record([
				'query' => $this->typeRegistry->string(1),
				'boundParameters' => $this->typeRegistry->alias(
					new TypeNameIdentifier('DatabaseQueryBoundParameters')
				)
			])
		);
		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('DatabaseQueryResultRow'),
			$this->typeRegistry->map(
				$this->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseValue')
				)
			)
		);
		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('DatabaseQueryResult'),
			$this->typeRegistry->array(
				$this->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseQueryResultRow')
				)
			)
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('DatabaseQueryFailure'),
			$this->typeRegistry->record([
				'query' => $this->typeRegistry->string(1),
				'boundParameters' => $this->typeRegistry->alias(
					new TypeNameIdentifier('DatabaseQueryBoundParameters')
				),
				'error' => $this->typeRegistry->string(),
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('DatabaseConnector'),
			$this->typeRegistry->record([
				'connection' => $this->typeRegistry->subtype(
					new TypeNameIdentifier('DatabaseConnection')
				)
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
	}

	/*
    protected function testMethodCallAnalyse(
        Type $targetType, string $methodName, Type $parameterType, Type $expectedType
    ): void {
        $call = $this->expressionRegistry->methodCall(
            $this->expressionRegistry->variableName(
                $var = new VariableNameIdentifier('x')
            ),
            new MethodNameIdentifier($methodName),
            $this->expressionRegistry->variableName(
                $var2 = new VariableNameIdentifier('y')
            )
        );
        $result = $call->analyse(VariableScope::fromPairs(
            new VariablePair(
                $var,
                $targetType
            ),
            new VariablePair(
                $var2,
                $parameterType
            )
        ));
        $this->assertTrue(
            $result->expressionType->isSubtypeOf($expectedType)
        );
    }

	protected function testMethodCall(
		Expression $target, string $methodName, Expression $parameter, Value $expectedValue
	): void {
		$call = $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier($methodName),
			$parameter
		);
		$call->analyse(VariableScope::empty());
		$this->assertTrue(
			($r = $call
				->execute(VariableValueScope::empty())
				->value())->equals($expectedValue),
			sprintf("'%s' is not equal to '%s'", $r, $expectedValue)
		);
	}
	*/
}