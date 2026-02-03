<?php

namespace Walnut\Lang\Test\Almond;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunctionFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeBuilder;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Code\Function\FunctionContextFiller;
use Walnut\Lang\Almond\Engine\Implementation\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\UnionTypeNormalizer;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Program\Validation\ValidationFactory;

abstract class AlmondBaseTestHelper extends TestCase {

	protected readonly ProgramContext $programContext;
	protected readonly TypeRegistry $typeRegistry;
	protected readonly UserlandTypeBuilder $userlandTypeBuilder;
	protected readonly ValueRegistry $valueRegistry;
	protected readonly ExpressionRegistry $expressionRegistry;
	protected readonly ValidationFactory $validationFactory;
	protected readonly UserlandFunctionFactory $userlandFunctionFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->programContext = new ProgramContextFactory()->newProgramContext();
		$this->typeRegistry = $this->programContext->typeRegistry;
		$this->userlandTypeBuilder = $this->programContext->userlandTypeBuilder;
		$this->valueRegistry = $this->programContext->valueRegistry;
		$this->expressionRegistry = $this->programContext->expressionRegistry;
		$this->validationFactory = $this->programContext->validationFactory;
		$this->userlandFunctionFactory = $this->programContext->userlandFunctionFactory;

		$this->addCoreToContext();
	}


	protected function addCoreToContext(): void {
		$this->userlandTypeBuilder->addAlias(
			new TypeName('CliEntryPoint'),
			$this->typeRegistry->function(
				$this->typeRegistry->array(
					$this->typeRegistry->string()
				),
				$this->typeRegistry->string()
			)
		);

		foreach(['NotANumber', 'MinusInfinity', 'PlusInfinity', 'DependencyContainer', 'Constructor'] as $atomType) {
			$this->userlandTypeBuilder->addAtom(
				new TypeName($atomType)
			);
		}

		$this->userlandTypeBuilder->addData(
			new TypeName('IntegerNumberIntervalEndpoint'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->integer(),
				'inclusive' => $this->typeRegistry->boolean
			], null),
		);
		$this->userlandTypeBuilder->addOpen(
			new TypeName('IntegerNumberInterval'),
			$this->typeRegistry->record([
				'start' => $this->typeRegistry->union([
					$this->typeRegistry->userland->atom(new TypeName('MinusInfinity')),
					$this->typeRegistry->userland->data(
						new TypeName('IntegerNumberIntervalEndpoint')
					)
				]),
				'end' => $this->typeRegistry->union([
					$this->typeRegistry->userland->atom(new TypeName('PlusInfinity')),
					$this->typeRegistry->userland->data(
						new TypeName('IntegerNumberIntervalEndpoint')
					)
				]),
			], null),
			null
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('IntegerNumberRange'),
			$this->typeRegistry->record(
				['intervals' => $this->typeRegistry->array(
					$this->typeRegistry->userland->open(new TypeName('IntegerNumberInterval')),
					1
				)], null
			)
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('RealNumberIntervalEndpoint'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->real(),
				'inclusive' => $this->typeRegistry->boolean
			], null),
		);
		$this->userlandTypeBuilder->addOpen(
			new TypeName('RealNumberInterval'),
			$this->typeRegistry->record([
				'start' => $this->typeRegistry->union([
					$this->typeRegistry->userland->atom(new TypeName('MinusInfinity')),
					$this->typeRegistry->userland->data(
						new TypeName('RealNumberIntervalEndpoint')
					)
				]),
				'end' => $this->typeRegistry->union([
					$this->typeRegistry->userland->atom(new TypeName('PlusInfinity')),
					$this->typeRegistry->userland->data(
						new TypeName('RealNumberIntervalEndpoint')
					)
				]),
			], null),
			null
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('RealNumberRange'),
			$this->typeRegistry->record(
				['intervals' => $this->typeRegistry->array(
					$this->typeRegistry->userland->open(new TypeName('RealNumberInterval')),
					1
				)], null
			)
		);



		$this->userlandTypeBuilder->addData(
			new TypeName('IndexOutOfRange'),
			$this->typeRegistry->record([
				'index' => $this->typeRegistry->integer()
			], null),
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('CastNotAvailable'),
			$this->typeRegistry->record([
				'from' => $this->typeRegistry->type($this->typeRegistry->any),
				'to' => $this->typeRegistry->type($this->typeRegistry->any),
			], null),
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('MapItemNotFound'),
			$this->typeRegistry->record([
				'key' => $this->typeRegistry->string()
			], null),
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('InvalidJsonValue'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any
			], null),
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('InvalidJsonString'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->string()
			], null),
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('UnknownEnumerationValue'),
			$this->typeRegistry->record([
				'enumeration' => $this->typeRegistry->type($this->typeRegistry->any),
				'value' => $this->typeRegistry->string(),
			], null),
		);
		$this->userlandTypeBuilder->addEnumeration(
			new TypeName('DependencyContainerErrorType'),
			[
				new EnumerationValueName('CircularDependency'),
				new EnumerationValueName('Ambiguous'),
				new EnumerationValueName('NotFound'),
				new EnumerationValueName('UnsupportedType'),
				new EnumerationValueName('ErrorWhileCreatingValue'),
			]
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('DependencyContainerError'),
			$this->typeRegistry->record([
				'targetType' => $this->typeRegistry->type($this->typeRegistry->any),
				'errorOnType' => $this->typeRegistry->type($this->typeRegistry->any),
				'errorMessage' => $this->typeRegistry->string(),
				'errorType' => $this->typeRegistry->userland->enumeration(
					new TypeName('DependencyContainerErrorType')
				)
			], null),
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('HydrationError'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->any,
				'hydrationPath' => $this->typeRegistry->string(),
				'errorMessage' => $this->typeRegistry->string(),
			], null),
		);

		$j = $this->typeRegistry->proxy(
			new TypeName('JsonValue')
		);

		$this->userlandTypeBuilder->addAlias(
			new TypeName('JsonValue'),
			new UnionType(
				new UnionTypeNormalizer($this->typeRegistry), [
				$this->typeRegistry->null,
				$this->typeRegistry->boolean,
				$this->typeRegistry->integer(),
				$this->typeRegistry->real(),
				$this->typeRegistry->string(),
				$this->typeRegistry->array($j),
				$this->typeRegistry->map($j),
				$this->typeRegistry->mutable($j)
			])
		);

		$this->userlandTypeBuilder->addData(
			new TypeName('DatabaseConnection'),
			$this->typeRegistry->record([
				'dsn' => $this->typeRegistry->string()
			], null),
		);
		$this->userlandTypeBuilder->addAlias(
			new TypeName('DatabaseValue'),
			$this->typeRegistry->union([
				$this->typeRegistry->string(),
				$this->typeRegistry->integer(),
				$this->typeRegistry->boolean,
				$this->typeRegistry->null
			])
		);

		$this->userlandTypeBuilder->addOpen(
			new TypeName('Uuid'),
			$this->typeRegistry->record([
				'value' => $this->typeRegistry->string()
			], null), null,
		);

		$this->userlandTypeBuilder->addAlias(
			new TypeName('DatabaseQueryBoundParameters'),
			$this->typeRegistry->union([
				$this->typeRegistry->array(
					$this->typeRegistry->typeByName(
						new TypeName('DatabaseValue')
					)
				),
				$this->typeRegistry->map(
					$this->typeRegistry->typeByName(
						new TypeName('DatabaseValue')
					)
				)
			])
		);
		$this->userlandTypeBuilder->addAlias(
			new TypeName('DatabaseQueryCommand'),
			$this->typeRegistry->record([
				'query' => $this->typeRegistry->string(1),
				'boundParameters' => $this->typeRegistry->userland->alias(
					new TypeName('DatabaseQueryBoundParameters')
				)
			], null)
		);
		$this->userlandTypeBuilder->addAlias(
			new TypeName('DatabaseQueryDataRow'),
			$this->typeRegistry->map(
				$this->typeRegistry->typeByName(
					new TypeName('DatabaseValue')
				)
			)
		);
		$this->userlandTypeBuilder->addAlias(
			new TypeName('DatabaseQueryResult'),
			$this->typeRegistry->array(
				$this->typeRegistry->typeByName(
					new TypeName('DatabaseQueryDataRow')
				)
			)
		);
		$this->userlandTypeBuilder->addData(
			new TypeName('DatabaseQueryFailure'),
			$this->typeRegistry->record([
				'query' => $this->typeRegistry->string(1),
				'boundParameters' => $this->typeRegistry->userland->alias(
					new TypeName('DatabaseQueryBoundParameters')
				),
				'error' => $this->typeRegistry->string(),
			], null),
		);
		$this->userlandTypeBuilder->addSealed(
			new TypeName('DatabaseConnector'),
			$this->typeRegistry->record([
				'connection' => $this->typeRegistry->userland->data(
					new TypeName('DatabaseConnection')
				)
			], null),
			null //$ef(),
		);

		//$[errorType: String, originalError: Any, errorMessage: String]
		$this->userlandTypeBuilder->addSealed(
			new TypeName('ExternalError'),
			$this->typeRegistry->record([
				'errorType' => $this->typeRegistry->string(),
				'originalError' => $this->typeRegistry->any,
				'errorMessage' => $this->typeRegistry->string(),
			], null),
			null //$ef(),
		);

		/*
$this->userlandFunctionFactory->create(
			null,
			null,
			null,
			null,
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableName('#'))
			)
		)
		*/
	}

	protected function addSampleTypes(): void {
		$i = fn(string $name) => new TypeName($name);
		$ev = fn(string $name) => new EnumerationValueName($name);
		$this->userlandTypeBuilder->addAlias($i('MyAlias'), $this->typeRegistry->null);
		$this->userlandTypeBuilder->addAlias($i('NonEmptyString'), $this->typeRegistry->string(1));
		$this->userlandTypeBuilder->addAtom($i('MyAtom'));
		$this->userlandTypeBuilder->addEnumeration($i('MyEnum'), [
			$ev('A'),
			$ev('B'),
			$ev('C')
		]);
		$this->userlandTypeBuilder->addSealed($i('MySealed'), $this->typeRegistry->null, null);
		$this->userlandTypeBuilder->addOpen($i('MyOpen'), $this->typeRegistry->null, null);
		$this->userlandTypeBuilder->addData($i('MyData'), $this->typeRegistry->null);
	}

}