<?php

namespace Walnut\Lang\Feature\Function;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MapType;
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
use Walnut\Lang\Blueprint\Value\TupleValue;
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
			MySealed := \$Real;
			MySealedTuple := \$[Boolean, ... Integer];
			MySealedRecord := \$[a: Real, b: ?String, c: ?String, ... Boolean];
			MyOpen := #Integer;
			MyOpenTuple := #[Boolean, ... String];
			MyOpenRecord := #[a: Real, b: ?String, c: ?String, ... Integer];
			MyData := #Integer;
			MyDataTuple := #[Boolean, ... String];
			MyDataRecord := #[a: Real, b: ?String, c: ?String, ... Integer];
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
			null,
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
			new VariableNameIdentifier('y'),
		);
		$this->assertCount(5, $filledAnalyserContext->variableScope->variables());
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
			null,
		);
		$this->assertCount(7, $filledAnalyserContext->variableScope->variables());
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
		$this->assertInstanceOf(
			ArrayType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%_'))
		);
		$this->assertInstanceOf(
			StringType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%_'))->itemType,
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
			null,
		);
		$this->assertCount(11, $filledAnalyserContext->variableScope->variables());
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
		$this->assertInstanceOf(
			MapType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%_'))
		);
		$this->assertInstanceOf(
			IntegerType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%_'))->itemType,
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
			null,
		);
		$this->assertCount(7, $filledAnalyserContext->variableScope->variables());
		$this->assertInstanceOf(
			RealType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$a'))
		);
		$this->assertInstanceOf(
			MapType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$_'))
		);
		$this->assertInstanceOf(
			BooleanType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$_'))->itemType,
		);
		$this->assertEquals(
			['$$', '$', '#', '$a', '$b', '$c', '$_'],
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
			null,
		);
		$this->assertCount(5, $filledAnalyserContext->variableScope->variables());
		$this->assertInstanceOf(
			BooleanType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$0'))
		);
		$this->assertInstanceOf(
			ArrayType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$_'))
		);
		$this->assertInstanceOf(
			IntegerType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('$_'))->itemType,
		);
		$this->assertEquals(
			['$$', '$', '#', '$0', '$_'],
			$filledAnalyserContext->variableScope->variables()
		);
	}


	public function testAnalyseSpreadPart5(): void {
		$analyserContext = $this->programContext->programRegistry->analyserContext;
		$filledAnalyserContext = $this->functionContextFiller->fillAnalyserContext(
			$analyserContext,
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyData')),
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyRecord')),
			null,
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('MyDataRecord')),
			null,
		);
		$this->assertCount(11, $filledAnalyserContext->variableScope->variables());
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
		$this->assertInstanceOf(
			MapType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%_'))
		);
		$this->assertInstanceOf(
			IntegerType::class,
			$filledAnalyserContext->variableScope->typeOf(new VariableNameIdentifier('%_'))->itemType,
		);
	}



	public function testExecuteEmpty(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			$this->programContext->typeRegistry->nothing,
			null,
			$this->programContext->typeRegistry->nothing,
			null,
			null,
			$this->programContext->typeRegistry->nothing,
			null,
			null,
		);
		$this->assertEquals($executionContext, $filledExecutionContext);
	}

	public function testExecuteBasic(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			$this->programContext->typeRegistry->boolean,
			($this->programContext->valueRegistry->boolean(true)),
			$this->programContext->typeRegistry->integer(),
			($this->programContext->valueRegistry->integer(123)),
			new VariableNameIdentifier('x'),
			$this->programContext->typeRegistry->string(),
			($this->programContext->valueRegistry->string('abc')),
			new VariableNameIdentifier('y'),
		);
		$this->assertCount(5, $filledExecutionContext->variableValueScope->variables());
		$this->assertInstanceOf(
			IntegerValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('x'))
		);
		$this->assertInstanceOf(
			StringValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('y'))
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
			$this->programContext->typeRegistry->sealed(
				new TypeNameIdentifier('MySealed')
			),
			($this->programContext->valueRegistry->sealedValue(
				new TypeNameIdentifier('MySealed'),
				$this->programContext->valueRegistry->real(1.23)
			)),
			$this->programContext->typeRegistry->tuple([
				$this->programContext->typeRegistry->string()
			]),
			($this->programContext->valueRegistry->tuple([
				$this->programContext->valueRegistry->string('str')
			])),
			null,
			$this->programContext->typeRegistry->open(
				new TypeNameIdentifier('MyOpenTuple')
			),
			($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpenTuple'),
				$this->programContext->valueRegistry->tuple([
					$this->programContext->valueRegistry->false,
					$this->programContext->valueRegistry->string('str'),
				])
			)),
			null,
		);
		$this->assertCount(7, $filledExecutionContext->variableValueScope->variables());
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
		$this->assertInstanceOf(
			TupleValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%_'))
		);
		$this->assertInstanceOf(
			StringValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%_'))->valueOf(0)
		);
	}

	public function testExecuteSpreadPart2(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			$this->programContext->typeRegistry->open(
				new TypeNameIdentifier('MyOpen')
			),
			($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpen'),
				$this->programContext->valueRegistry->integer(42)
			)),
			$this->programContext->typeRegistry->record([
				'a' => $this->programContext->typeRegistry->boolean,
				'b' => $this->programContext->typeRegistry->optionalKey(
					$this->programContext->typeRegistry->integer()
				),
				'c' => $this->programContext->typeRegistry->optionalKey(
					$this->programContext->typeRegistry->integer()
				),
			]),
			(
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->true,
					'b' => $this->programContext->valueRegistry->integer(99),
				])
			),
			null,
			$this->programContext->typeRegistry->open(
				new TypeNameIdentifier('MyOpenRecord')
			),
			($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpenRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(-1.5),
					'b' => $this->programContext->valueRegistry->string('str'),
					'd' => $this->programContext->valueRegistry->integer(42),
				])
			)),
			null,
		);
		$this->assertCount(11, $filledExecutionContext->variableValueScope->variables());
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
		$this->assertInstanceOf(
			RecordValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%_'))
		);
		$this->assertInstanceOf(
			IntegerValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('%_'))->valueOf('d')
		);
	}

	public function testExecuteSpreadPart3(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			($this->programContext->typeRegistry->sealed(
				new TypeNameIdentifier('MySealedRecord')
			)),
			($this->programContext->valueRegistry->sealedValue(
				new TypeNameIdentifier('MySealedRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
					'd' => $this->programContext->valueRegistry->true,
				]),
			)),
			($this->programContext->typeRegistry->sealed(
				new TypeNameIdentifier('MySealedRecord')
			)),
			($this->programContext->valueRegistry->sealedValue(
				new TypeNameIdentifier('MySealedRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str')
				]),
			)),
			null,
			$this->programContext->typeRegistry->nothing,
			null,
			null,
		);
		$this->assertCount(7, $filledExecutionContext->variableValueScope->variables());
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
			RecordValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$_'))
		);
		$this->assertInstanceOf(
			BooleanValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$_'))->valueOf('d')
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
			$this->programContext->typeRegistry->open(
				new TypeNameIdentifier('MyOpenRecord')
			),
			($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpenRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
				]),
			)),
			$this->programContext->typeRegistry->open(
				new TypeNameIdentifier('MyOpenRecord')
			),
			($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpenRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
				]),
			)),
			null,
			$this->programContext->typeRegistry->nothing,
			null,
			null,
		);
		$this->assertCount(11, $filledExecutionContext->variableValueScope->variables());
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
			RecordValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$_'))
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

	public function testExecuteSpreadPart5(): void {
		$executionContext = $this->programContext->programRegistry->executionContext;
		$filledExecutionContext = $this->functionContextFiller->fillExecutionContext(
			$executionContext,
			$this->programContext->typeRegistry->open(
				new TypeNameIdentifier('MyDataRecord')
			),
			($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyDataRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
				]),
			)),
			$this->programContext->typeRegistry->open(
				new TypeNameIdentifier('MyDataRecord')
			),
			($this->programContext->valueRegistry->openValue(
				new TypeNameIdentifier('MyDataRecord'),
				$this->programContext->valueRegistry->record([
					'a' => $this->programContext->valueRegistry->real(3.14),
					'b' => $this->programContext->valueRegistry->string('str'),
				]),
			)),
			null,
			$this->programContext->typeRegistry->nothing,
			null,
			null,
		);
		$this->assertCount(11, $filledExecutionContext->variableValueScope->variables());
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
			RecordValue::class,
			$filledExecutionContext->variableValueScope->valueOf(new VariableNameIdentifier('$_'))
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