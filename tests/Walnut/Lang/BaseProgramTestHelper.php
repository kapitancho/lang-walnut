<?php

namespace Walnut\Lang\Test;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ProgramBuilder as ProgramBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Factory\ProgramFactory as ProgramFactoryInterface;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Implementation\Program\Factory\ProgramFactory;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\UnionType;

abstract class BaseProgramTestHelper extends TestCase {
	protected TypeRegistry $typeRegistry;
	protected ValueRegistry $valueRegistry;
	protected ExpressionRegistry $expressionRegistry;
	protected TypeRegistryBuilder $typeRegistryBuilder;
	protected CustomMethodRegistryBuilder $customMethodRegistryBuilder;
	protected ScopeBuilder $globalScopeBuilder;
	protected ProgramBuilderInterface $programBuilder;

	private function getProgramFactory(): ProgramFactoryInterface {
		return new ProgramFactory();
	}

	protected function setUp(): void {
		parent::setUp();

		$programFactory = $this->getProgramFactory();
		$programBuilder = $programFactory->builder();
		$programRegistry = $programFactory->registry();
		$this->typeRegistry = $programRegistry->typeRegistry();
		$this->valueRegistry = $programRegistry->valueRegistry();
		$this->expressionRegistry = $programRegistry->expressionRegistry();
		$this->programBuilder = $programBuilder;

		$this->addCoreToContext();
	}
	
	protected function addCoreToContext(): void {
		foreach(['NotANumber', 'MinusInfinity', 'PlusInfinity', 'DependencyContainer', 'Constructor'] as $atomType) {
			$this->typeRegistry->addAtom(
				new TypeNameIdentifier($atomType)
			);
		}
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('IndexOutOfRange'),
			$this->typeRegistry->record([
				'index' => $this->typeRegistry->integer()
			])
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('CastNotAvailable'),
			$this->typeRegistry->record([
				'from' => $this->typeRegistry->type($this->typeRegistry->any()),
				'to' => $this->typeRegistry->type($this->typeRegistry->any()),
			]),
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('MapItemNotFound'),
			$this->typeRegistry->record([
				'key' => $this->typeRegistry->string()
			]),
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('InvalidJsonValue'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any()
			]),
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('InvalidJsonString'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->string()
			]),
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('UnknownEnumerationValue'),
			$this->typeRegistry->record([
				'enumeration' => $this->typeRegistry->type($this->typeRegistry->any()),
				'value' => $this->typeRegistry->string(),
			]),
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('DependencyContainerError'),
			$this->typeRegistry->record([
				'targetType' => $this->typeRegistry->type($this->typeRegistry->any()),
				'errorMessage' => $this->typeRegistry->string(),
			]),
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('HydrationError'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any(),
				'hydrationPath' => $this->typeRegistry->string(),
				'errorMessage' => $this->typeRegistry->string(),
			]),
		);

		$j = $this->typeRegistry->proxyType(
			new TypeNameIdentifier('JsonValue')
		);

		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('JsonValue'),
			new UnionType(
				new UnionTypeNormalizer($this->typeRegistry),
				$this->typeRegistry->null(),
				$this->typeRegistry->boolean(),
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
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->null()
				)
			),
			null
		);
		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('DatabaseValue'),
			$this->typeRegistry->union([
				$this->typeRegistry->string(),
				$this->typeRegistry->integer(),
				$this->typeRegistry->boolean(),
				$this->typeRegistry->null()
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
		);
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('DatabaseConnector'),
			$this->typeRegistry->record([
				'connection' => $this->typeRegistry->subtype(
					new TypeNameIdentifier('DatabaseConnection')
				)
			])
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