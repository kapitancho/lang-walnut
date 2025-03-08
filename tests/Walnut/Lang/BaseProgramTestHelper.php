<?php

namespace Walnut\Lang\Test;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder as ScopeBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Blueprint\Program\Program as ProgramInterface;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\UnionType;

abstract class BaseProgramTestHelper extends TestCase {
	protected TypeRegistry $typeRegistry;
	protected ValueRegistry $valueRegistry;
	protected ExpressionRegistry $expressionRegistry;
	protected TypeRegistryBuilder $typeRegistryBuilder;
	protected CustomMethodRegistryBuilder $customMethodRegistryBuilder;
	protected ScopeBuilderInterface $globalScopeBuilder;
	protected ProgramContextInterface $programContext;

	private function getProgramContext(): ProgramContextInterface {
		return new ProgramContextFactory()->programContext;
	}

	protected function setUp(): void {
		parent::setUp();

		$this->programContext = $programContext = $this->getProgramContext();
		$this->globalScopeBuilder = $programContext->globalScopeBuilder;
		$this->typeRegistry = $programContext->typeRegistry;
		$this->typeRegistryBuilder = $programContext->typeRegistryBuilder;
		$this->valueRegistry = $programContext->valueRegistry;
		$this->expressionRegistry = $programContext->expressionRegistry;
		$this->customMethodRegistryBuilder = $programContext->customMethodRegistryBuilder;

		$this->addCoreToContext();
	}

	protected ProgramRegistry $programRegistry {
		get => $this->programContext->programRegistry;
	}

	protected ProgramInterface $program {
		get => $this->programContext->analyseAndBuildProgram();
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
		$this->typeRegistry->addOpen(
			new TypeNameIdentifier('IndexOutOfRange'),
			$this->typeRegistry->record([
				'index' => $this->typeRegistry->integer()
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addOpen(
			new TypeNameIdentifier('CastNotAvailable'),
			$this->typeRegistry->record([
				'from' => $this->typeRegistry->type($this->typeRegistry->any),
				'to' => $this->typeRegistry->type($this->typeRegistry->any),
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addOpen(
			new TypeNameIdentifier('MapItemNotFound'),
			$this->typeRegistry->record([
				'key' => $this->typeRegistry->string()
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addOpen(
			new TypeNameIdentifier('InvalidJsonValue'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addOpen(
			new TypeNameIdentifier('InvalidJsonString'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->string()
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addOpen(
			new TypeNameIdentifier('UnknownEnumerationValue'),
			$this->typeRegistry->record([
				'enumeration' => $this->typeRegistry->type($this->typeRegistry->any),
				'value' => $this->typeRegistry->string(),
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistryBuilder->addEnumeration(
			new TypeNameIdentifier('DependencyContainerErrorType'),
			[
				new EnumValueIdentifier('CircularDependency'),
				new EnumValueIdentifier('Ambiguous'),
				new EnumValueIdentifier('NotFound'),
				new EnumValueIdentifier('UnsupportedType'),
				new EnumValueIdentifier('ErrorWhileCreatingValue'),
			]
		);
		$this->typeRegistryBuilder->addSealed(
			new TypeNameIdentifier('DependencyContainerError'),
			$this->typeRegistry->record([
				'targetType' => $this->typeRegistry->type($this->typeRegistry->any),
				'errorOnType' => $this->typeRegistry->type($this->typeRegistry->any),
				'errorMessage' => $this->typeRegistry->string(),
				'errorType' => $this->typeRegistry->enumeration(
					new TypeNameIdentifier('DependencyContainerErrorType')
				)
			]),
			$ef(),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addOpen(
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

		$this->typeRegistry->addOpen(
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
		$this->typeRegistry->addOpen(
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
				'connection' => $this->typeRegistry->open(
					new TypeNameIdentifier('DatabaseConnection')
				)
			]),
			$ef(),
			$this->typeRegistry->nothing
		);

		//$[errorType: String, originalError: Any, errorMessage: String]
		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('ExternalError'),
			$this->typeRegistry->record([
				'errorType' => $this->typeRegistry->string(),
				'originalError' => $this->typeRegistry->any,
				'errorMessage' => $this->typeRegistry->string(),
			]),
			$ef(),
			$this->typeRegistry->nothing
		);

	}


	protected function addSampleTypes(): void {
		$i = fn(string $name) => new TypeNameIdentifier($name);
		$ev = fn(string $name) => new EnumValueIdentifier($name);
		$this->typeRegistryBuilder->addAlias($i('MyAlias'), $this->typeRegistry->null);
		$this->typeRegistryBuilder->addAtom($i('MyAtom'));
		$this->typeRegistryBuilder->addEnumeration($i('MyEnum'), [
			$ev('A'),
			$ev('B'),
			$ev('C')
		]);
		$this->typeRegistryBuilder->addSealed($i('MySealed'), $this->typeRegistry->null);
		$this->typeRegistryBuilder->addOpen($i('MyOpen'), $this->typeRegistry->null);
		$this->typeRegistryBuilder->addSubset($i('MySubset'), $this->typeRegistry->null);
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