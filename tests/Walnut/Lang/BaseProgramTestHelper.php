<?php

namespace Walnut\Lang\Test;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\ProgramContext as ProgramContextInterface;
use Walnut\Lang\Blueprint\Program\Program as ProgramInterface;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\UnionType;

abstract class BaseProgramTestHelper extends TestCase {
	protected TypeRegistry $typeRegistry;
	protected ValueRegistry $valueRegistry;
	protected ExpressionRegistry $expressionRegistry;
	protected TypeRegistryBuilder $typeRegistryBuilder;
	protected CustomMethodRegistryBuilder $customMethodRegistryBuilder;
	protected ProgramContextInterface $programContext;

	private function getProgramContext(): ProgramContextInterface {
		return new ProgramContextFactory()->programContext;
	}

	protected function setUp(): void {
		parent::setUp();

		$this->programContext = $programContext = $this->getProgramContext();
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

	protected AnalyserContext $analyserContext {
		get => $this->programRegistry->analyserContext;
	}

	protected ExecutionContext $executionContext {
		get => $this->programRegistry->executionContext;
	}

	protected ProgramInterface $program {
		get => $this->programContext->analyseAndBuildProgram();
	}

	protected function addCoreToContext(): void {
		$this->typeRegistryBuilder->addAlias(
			new TypeNameIdentifier('CliEntryPoint'),
			$this->typeRegistry->function(
				$this->typeRegistry->array(
					$this->typeRegistry->string()
				),
				$this->typeRegistry->string()
			)
		);

		foreach(['NotANumber', 'MinusInfinity', 'PlusInfinity', 'DependencyContainer', 'Constructor'] as $atomType) {
			$this->typeRegistryBuilder->addAtom(
				new TypeNameIdentifier($atomType)
			);
		}
		$ef = fn() => $this->expressionRegistry->functionBody(
			$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
		);

		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('IntegerNumberIntervalEndpoint'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->integer(),
				'inclusive' => $this->typeRegistry->boolean
			]),
		);
		$this->typeRegistryBuilder->addOpen(
			new TypeNameIdentifier('IntegerNumberInterval'),
			$this->typeRegistry->record([
				'start' => $this->typeRegistry->union([
					$this->typeRegistry->complex->atom(new TypeNameIdentifier('MinusInfinity')),
					$this->typeRegistry->complex->data(
						new TypeNameIdentifier('IntegerNumberIntervalEndpoint')
					)
				]),
				'end' => $this->typeRegistry->union([
					$this->typeRegistry->complex->atom(new TypeNameIdentifier('PlusInfinity')),
					$this->typeRegistry->complex->data(
						new TypeNameIdentifier('IntegerNumberIntervalEndpoint')
					)
				]),
			]),
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('IntegerNumberRange'),
			$this->typeRegistry->record(
				['intervals' => $this->typeRegistry->array(
					$this->typeRegistry->complex->open(new TypeNameIdentifier('IntegerNumberInterval')),
					1
				)]
			)
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('RealNumberIntervalEndpoint'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->real(),
				'inclusive' => $this->typeRegistry->boolean
			]),
		);
		$this->typeRegistryBuilder->addOpen(
			new TypeNameIdentifier('RealNumberInterval'),
			$this->typeRegistry->record([
				'start' => $this->typeRegistry->union([
					$this->typeRegistry->complex->atom(new TypeNameIdentifier('MinusInfinity')),
					$this->typeRegistry->complex->data(
						new TypeNameIdentifier('RealNumberIntervalEndpoint')
					)
				]),
				'end' => $this->typeRegistry->union([
					$this->typeRegistry->complex->atom(new TypeNameIdentifier('PlusInfinity')),
					$this->typeRegistry->complex->data(
						new TypeNameIdentifier('RealNumberIntervalEndpoint')
					)
				]),
			]),
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('RealNumberRange'),
			$this->typeRegistry->record(
				['intervals' => $this->typeRegistry->array(
					$this->typeRegistry->complex->open(new TypeNameIdentifier('RealNumberInterval')),
					1
				)]
			)
		);



		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('IndexOutOfRange'),
			$this->typeRegistry->record([
				'index' => $this->typeRegistry->integer()
			]),
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('CastNotAvailable'),
			$this->typeRegistry->record([
				'from' => $this->typeRegistry->type($this->typeRegistry->any),
				'to' => $this->typeRegistry->type($this->typeRegistry->any),
			]),
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('MapItemNotFound'),
			$this->typeRegistry->record([
				'key' => $this->typeRegistry->string()
			]),
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('InvalidJsonValue'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any
			]),
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('InvalidJsonString'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->string()
			]),
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('UnknownEnumerationValue'),
			$this->typeRegistry->record([
				'enumeration' => $this->typeRegistry->type($this->typeRegistry->any),
				'value' => $this->typeRegistry->string(),
			]),
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
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('DependencyContainerError'),
			$this->typeRegistry->record([
				'targetType' => $this->typeRegistry->type($this->typeRegistry->any),
				'errorOnType' => $this->typeRegistry->type($this->typeRegistry->any),
				'errorMessage' => $this->typeRegistry->string(),
				'errorType' => $this->typeRegistry->complex->enumeration(
					new TypeNameIdentifier('DependencyContainerErrorType')
				)
			]),
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('HydrationError'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any,
				'hydrationPath' => $this->typeRegistry->string(),
				'errorMessage' => $this->typeRegistry->string(),
			]),
		);

		$j = $this->typeRegistry->proxyType(
			new TypeNameIdentifier('JsonValue')
		);

		$this->typeRegistryBuilder->addAlias(
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

		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('DatabaseConnection'),
			$this->typeRegistry->record([
				'dsn' => $this->typeRegistry->string()
			]),
		);
		$this->typeRegistryBuilder->addAlias(
			new TypeNameIdentifier('DatabaseValue'),
			$this->typeRegistry->union([
				$this->typeRegistry->string(),
				$this->typeRegistry->integer(),
				$this->typeRegistry->boolean,
				$this->typeRegistry->null
			])
		);

		$this->typeRegistryBuilder->addOpen(
			new TypeNameIdentifier('Uuid'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->string()
			]),
		);

		$this->typeRegistryBuilder->addAlias(
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
		$this->typeRegistryBuilder->addAlias(
			new TypeNameIdentifier('DatabaseQueryCommand'),
			$this->typeRegistry->record([
				'query' => $this->typeRegistry->string(1),
				'boundParameters' => $this->typeRegistry->complex->alias(
					new TypeNameIdentifier('DatabaseQueryBoundParameters')
				)
			])
		);
		$this->typeRegistryBuilder->addAlias(
			new TypeNameIdentifier('DatabaseQueryDataRow'),
			$this->typeRegistry->map(
				$this->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseValue')
				)
			)
		);
		$this->typeRegistryBuilder->addAlias(
			new TypeNameIdentifier('DatabaseQueryResult'),
			$this->typeRegistry->array(
				$this->typeRegistry->withName(
					new TypeNameIdentifier('DatabaseQueryDataRow')
				)
			)
		);
		$this->typeRegistryBuilder->addData(
			new TypeNameIdentifier('DatabaseQueryFailure'),
			$this->typeRegistry->record([
				'query' => $this->typeRegistry->string(1),
				'boundParameters' => $this->typeRegistry->complex->alias(
					new TypeNameIdentifier('DatabaseQueryBoundParameters')
				),
				'error' => $this->typeRegistry->string(),
			]),
		);
		$this->typeRegistryBuilder->addSealed(
			new TypeNameIdentifier('DatabaseConnector'),
			$this->typeRegistry->record([
				'connection' => $this->typeRegistry->complex->data(
					new TypeNameIdentifier('DatabaseConnection')
				)
			]),
			$ef(),
			$this->typeRegistry->nothing
		);

		//$[errorType: String, originalError: Any, errorMessage: String]
		$this->typeRegistryBuilder->addSealed(
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
		$this->typeRegistryBuilder->addAlias($i('NonEmptyString'), $this->typeRegistry->string(1));
		$this->typeRegistryBuilder->addAtom($i('MyAtom'));
		$this->typeRegistryBuilder->addEnumeration($i('MyEnum'), [
			$ev('A'),
			$ev('B'),
			$ev('C')
		]);
		$this->typeRegistryBuilder->addSealed($i('MySealed'), $this->typeRegistry->null);
		$this->typeRegistryBuilder->addOpen($i('MyOpen'), $this->typeRegistry->null);
		$this->typeRegistryBuilder->addData($i('MyData'), $this->typeRegistry->null);
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


	protected function addCliEntryPoint(Value $value): void {
		$this->programContext->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->typeByName(new TypeNameIdentifier('DependencyContainer')),
			new MethodNameIdentifier('asCliEntryPoint'),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->null,
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->typeByName(new TypeNameIdentifier('CliEntryPoint')),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant($value)
			),
		);
	}

}