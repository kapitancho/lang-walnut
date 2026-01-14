<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class FunctionCallExpressionTest extends BaseProgramTestHelper {

	private MethodCallExpression $functionCallExpression;
	private FunctionValue $functionValue;

	private function functionCall(Expression $target, Expression $parameter): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier('invoke'),
			$parameter
		);
	}

	protected function setUp(): void {
		parent::setUp();
		$this->functionCallExpression = $this->functionCall(
			$this->expressionRegistry->variableName(new VariableNameIdentifier('a')),
			$this->expressionRegistry->variableName(new VariableNameIdentifier('b')),
		);
		$this->typeRegistryBuilder->addSealed(
			new TypeNameIdentifier("MyCustomType"),
			$this->typeRegistry->record(['x' => $this->typeRegistry->integer()]),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->null
				),
			),
			$this->typeRegistry->nothing
		);
		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->withName(new TypeNameIdentifier('MyCustomType')),
			new MethodNameIdentifier('invoke'),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->integer(),
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->string(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->string("hi")
				)
			)
		);
		$this->functionValue = $this->valueRegistry->function(
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->integer(),
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->string(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->string("hi")
				)
			)
		);
	}
	
	public function testAnalyseDefault(): void {
		$result = $this->functionCallExpression->analyse(
			$this->programRegistry->analyserContext->withAddedVariableType(
				new VariableNameIdentifier('a'),
				$this->typeRegistry->function(
					$this->typeRegistry->integer(),
					$this->typeRegistry->string(),
				)
			)->withAddedVariableType(
				new VariableNameIdentifier('b'),
				$this->typeRegistry->integer()
			)
		);
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType
		);
	}

	public function testAnalyseOnCustomType(): void {
		$result = $this->functionCallExpression->analyse(
			$this->programRegistry->analyserContext->withAddedVariableType(
				new VariableNameIdentifier('a'),
				$this->typeRegistry->withName(new TypeNameIdentifier('MyCustomType'))
			)->withAddedVariableType(
				new VariableNameIdentifier('b'),
				$this->typeRegistry->integer()
			)
		);
		self::assertTrue($result->expressionType->isSubtypeOf($this->typeRegistry->string()));
	}

	public function testAnalyseFailWrongParameter(): void {
		$this->expectException(AnalyserException::class);
		$this->functionCallExpression->analyse(
			$this->programRegistry->analyserContext->withAddedVariableType(
				new VariableNameIdentifier('a'),
				$this->typeRegistry->function(
					$this->typeRegistry->integer(),
					$this->typeRegistry->string(),
				)
			)->withAddedVariableType(
				new VariableNameIdentifier('b'),
				$this->typeRegistry->boolean
			)
		);
	}

	public function testAnalyseFailWrongType(): void {
		$this->expectException(AnalyserException::class);
		$this->functionCallExpression->analyse(
			$this->programRegistry->analyserContext->withAddedVariableType(
				new VariableNameIdentifier('a'),
				$this->typeRegistry->integer()
			)->withAddedVariableType(
				new VariableNameIdentifier('b'),
				$this->typeRegistry->integer()
			)
		);
	}
	public function testExecuteDefault(): void {
		$result = $this->functionCallExpression->execute(
			$this->programRegistry->executionContext
				->withAddedVariableValue(
					new VariableNameIdentifier('a'),
					$this->functionValue
				)
				->withAddedVariableValue(
					new VariableNameIdentifier('b'),
					$this->valueRegistry->integer(1)
				)
		);
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteOnCustomType(): void {
		$result = $this->functionCallExpression->execute(
			$this->programRegistry->executionContext
				->withAddedVariableValue(
					new VariableNameIdentifier('a'),
					$this->valueRegistry->sealedValue(
						new TypeNameIdentifier('MyCustomType'),
						$this->valueRegistry->record(['x' => $this->valueRegistry->integer(1)])
					)
				)->withAddedVariableValue(
					new VariableNameIdentifier('b'),
					$this->valueRegistry->integer(1)
				)
		);
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteFailWrongType(): void {
		$this->expectException(ExecutionException::class);
		$this->functionCallExpression->execute(
			$this->programRegistry->executionContext
				->withAddedVariableValue(
					new VariableNameIdentifier('a'),
					$this->valueRegistry->integer(1)
				)->withAddedVariableValue(
					new VariableNameIdentifier('b'),
					$this->valueRegistry->integer(1)
				)
		);
	}
}