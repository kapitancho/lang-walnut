<?php

namespace Walnut\Lang\Test\Feature\Proto;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Function\FunctionContextFiller;
use Walnut\Lang\Test\Feature\ProgramContextTestHelper;

final class FunctionContextFillerTest extends ProgramContextTestHelper {

	private FunctionContextFiller $functionContextFiller;

	public function setUp(): void {
		parent::setUp();

		$this->functionContextFiller = new FunctionContextFiller();
	}

	protected function getTestCode(): string {
		return <<<NUT
			MyTuple = [String];
			MyRecord = [a: Boolean, b: ?Integer, c: ?Integer];
			MySealed = \$Real;
			MySealedTuple = \$[Boolean];
			MySealedRecord = \$[a: Real, b: ?String, c: ?String];
			MyOpen = #Integer;
			MyOpenTuple = #[Boolean];
			MyOpenRecord = #[a: Real, b: ?String, c: ?String];
		NUT;
	}

	public function testAnalyseEmpty(): void {
		$analyserContext = $this->programContext->programRegistry->analyserContext;
		$filledAnalyserContext = $this->functionContextFiller->fillAnalyserContext(
			$analyserContext,
			$this->programContext->typeRegistry->nothing,
			$this->programContext->typeRegistry->nothing,
			null,
			$this->programContext->typeRegistry->nothing,
		);
		$this->assertEquals($analyserContext, $filledAnalyserContext);
	}

	public function testAnalyseBasic(): void {
		$analyserContext = $this->programContext->programRegistry->analyserContext;
		$filledAnalyserContext = $this->functionContextFiller->fillAnalyserContext(
			$analyserContext,
			$this->programContext->typeRegistry->boolean,
			$this->programContext->typeRegistry->integer(),
			new VariableNameIdentifier('x'),
			$this->programContext->typeRegistry->string(),
		);
		$this->assertCount(4, $filledAnalyserContext->variableScope->variables());
		$this->assertInstanceOf(
			IntegerType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('x'))
		);
		$this->assertInstanceOf(
			IntegerType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('#'))
		);
		$this->assertInstanceOf(
			BooleanType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$'))
		);
		$this->assertInstanceOf(
			StringType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%'))
		);
	}

	public function testAnalyseSpreadPart1(): void {
		$analyserContext = $this->programContext->programRegistry->analyserContext;
		$filledAnalyserContext = $this->functionContextFiller->fillAnalyserContext(
			$analyserContext,
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MySealed')),
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyTuple')),
			null,
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyOpenTuple')),
		);
		$this->assertCount(6, $filledAnalyserContext->variableScope->variables());
		$this->assertInstanceOf(
			RealType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$$'))
		);
		$this->assertInstanceOf(
			StringType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('#0'))
		);
		$this->assertInstanceOf(
			BooleanType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%0'))
		);
	}

	public function testAnalyseSpreadPart2(): void {
		$analyserContext = $this->programContext->programRegistry->analyserContext;
		$filledAnalyserContext = $this->functionContextFiller->fillAnalyserContext(
			$analyserContext,
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyOpen')),
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyRecord')),
			null,
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyOpenRecord')),
		);
		$this->assertCount(10, $filledAnalyserContext->variableScope->variables());
		$this->assertInstanceOf(
			IntegerType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$$'))
		);
		$this->assertInstanceOf(
			BooleanType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('#a'))
		);
		$this->assertInstanceOf(
			RealType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%a'))
		);
	}

	public function testAnalyseSpreadPart3(): void {
		$analyserContext = $this->programContext->programRegistry->analyserContext;
		$filledAnalyserContext = $this->functionContextFiller->fillAnalyserContext(
			$analyserContext,
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MySealedRecord')),
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MySealedRecord')),
			null,
			$this->programContext->typeRegistry->nothing,
		);
		$this->assertCount(6, $filledAnalyserContext->variableScope->variables());
		$this->assertInstanceOf(
			RealType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$a'))
		);
		$this->assertEquals(
			['$$', '$', '#', '$a', '$b', '$c'],
			$filledAnalyserContext->variableScope->variables()
		);
	}

	public function testAnalyseSpreadPart4(): void {
		$analyserContext = $this->programContext->programRegistry->analyserContext;
		$filledAnalyserContext = $this->functionContextFiller->fillAnalyserContext(
			$analyserContext,
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MySealedTuple')),
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MySealedTuple')),
			null,
			$this->programContext->typeRegistry->nothing,
		);
		$this->assertCount(4, $filledAnalyserContext->variableScope->variables());
		$this->assertInstanceOf(
			BooleanType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$0'))
		);
		$this->assertEquals(
			['$$', '$', '#', '$0'],
			$filledAnalyserContext->variableScope->variables()
		);
	}




	public function testExecuteEmpty(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			null,
			null,
			null,
			null,
		);
		$this->assertEquals($executionContext, $filledExecutionContext);
	}

	public function testExecuteBasic(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			TypedValue::forValue($this->programContext->valueRegistry->boolean(true)),
			TypedValue::forValue($this->programContext->valueRegistry->integer(123)),
			new VariableNameIdentifier('x'),
			TypedValue::forValue($this->programContext->valueRegistry->string('abc')),
		);
		$this->assertCount(4, $filledExecutionContext->variableValueScope->variables());
		$this->assertInstanceOf(
			IntegerValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('x'))
		);
		$this->assertInstanceOf(
			IntegerValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#'))
		);
		$this->assertInstanceOf(
			BooleanValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$'))
		);
		$this->assertInstanceOf(
			StringValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%'))
		);
	}

	public function testExecuteSpreadPart1(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			TypedValue::forValue($this->programContext->valueRegistry->sealedValue(
				new TypeNameIdentifier('MySealed'),
				$this->programContext->valueRegistry->real(1.23)
			)),
			TypedValue::forValue($this->programContext->valueRegistry->tuple([
				$this->programContext->valueRegistry->string('str')
			])),
			null,
			TypedValue::forValue($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpenTuple'),
				$this->programContext->valueRegistry->tuple([
					$this->programContext->valueRegistry->false
				])
			)),
		);
		$this->assertCount(6, $filledExecutionContext->variableValueScope->variables());
		$this->assertInstanceOf(
			RealValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$$'))
		);
		$this->assertInstanceOf(
			StringValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#0'))
		);
		$this->assertInstanceOf(
			BooleanValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%0'))
		);
	}

	public function testExecuteSpreadPart2(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			TypedValue::forValue($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpen'),
				$this->programContext->valueRegistry->integer(42)
			)),
			TypedValue::forValue(
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->true,
					'b' => $this->programContext->valueRegistry->integer(99),
				])
			)->withType(
				$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyRecord')),
			),
			null,
			TypedValue::forValue($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpenRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(-1.5),
					'b' => $this->programContext->valueRegistry->string('str'),
				])
			)),
		);
		$this->assertCount(10, $filledExecutionContext->variableValueScope->variables());
		$this->assertInstanceOf(
			IntegerValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$$'))
		);
		$this->assertInstanceOf(
			BooleanValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#a'))
		);
		$this->assertInstanceOf(
			IntegerValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#b'))
		);
		$this->assertInstanceOf(
			ErrorValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#c'))
		);
		$this->assertInstanceOf(
			RealValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%a'))
		);
		$this->assertInstanceOf(
			StringValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%b'))
		);
		$this->assertInstanceOf(
			ErrorValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%c'))
		);
	}

	public function testExecuteSpreadPart3(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			TypedValue::forValue($this->programContext->valueRegistry->sealedValue(
				new TypeNameIdentifier('MySealedRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
				]),
			)),
			TypedValue::forValue($this->programContext->valueRegistry->sealedValue(
				new TypeNameIdentifier('MySealedRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
				]),
			)),
			null,
			null,
		);
		$this->assertCount(6, $filledExecutionContext->variableValueScope->variables());
		$this->assertInstanceOf(
			RecordValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$$'))
		);
		$this->assertInstanceOf(
			SealedValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$'))
		);
		$this->assertInstanceOf(
			RealValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$a'))
		);
		$this->assertInstanceOf(
			StringValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$b'))
		);
		$this->assertInstanceOf(
			ErrorValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$c'))
		);
		$this->assertInstanceOf(
			SealedValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#'))
		);
	}

	public function testExecuteSpreadPart4(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			TypedValue::forValue($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpenRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
				]),
			)),
			TypedValue::forValue($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpenRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
				]),
			)),
			null,
			null,
		);
		$this->assertCount(9, $filledExecutionContext->variableValueScope->variables());
		$this->assertInstanceOf(
			RecordValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$$'))
		);
		$this->assertInstanceOf(
			OpenValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$'))
		);
		$this->assertInstanceOf(
			RealValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$a'))
		);
		$this->assertInstanceOf(
			StringValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$b'))
		);
		$this->assertInstanceOf(
			ErrorValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$c'))
		);
		$this->assertInstanceOf(
			OpenValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#'))
		);
		$this->assertInstanceOf(
			RealValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#a'))
		);
		$this->assertInstanceOf(
			StringValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#b'))
		);
		$this->assertInstanceOf(
			ErrorValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('#c'))
		);
	}
}