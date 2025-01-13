<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
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
			$this->typeRegistry->integer(),
			$this->typeRegistry->nothing,
			$this->typeRegistry->string(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->string("hi")
				)
			)
		);
		$this->typeRegistry->addSubtype(
			new TypeNameIdentifier("MyFunction"),
			$this->typeRegistry->function(
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->null
				),
			),
			$this->typeRegistry->nothing
		);
		$this->functionValue = $this->valueRegistry->function(
			$this->typeRegistry->integer(),
			$this->typeRegistry->nothing,
			$this->typeRegistry->string(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->string("hi")
				)
			)
		);
	}
	
	public function testAnalyseDefault(): void {
		$result = $this->functionCallExpression->analyse(new AnalyserContext($this->programRegistry, new VariableScope([
			'a' => $this->typeRegistry->function(
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			),
			'b' => $this->typeRegistry->integer()
		])));
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType
		);
	}

	public function testAnalyseOnSubtypes(): void {
		$result = $this->functionCallExpression->analyse(new AnalyserContext($this->programRegistry, new VariableScope([
			'a' => $this->typeRegistry->withName(new TypeNameIdentifier('MyFunction')),
			'b' => $this->typeRegistry->integer()
		])));
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType
		);
	}

	public function testAnalyseOnCustomType(): void {
		$result = $this->functionCallExpression->analyse(new AnalyserContext($this->programRegistry, new VariableScope([
			'a' => $this->typeRegistry->withName(new TypeNameIdentifier('MyCustomType')),
			'b' => $this->typeRegistry->integer()
		])));
		self::assertTrue($result->expressionType->isSubtypeOf($this->typeRegistry->string()));
	}

	public function testAnalyseFailWrongParameter(): void {
		$this->expectException(AnalyserException::class);
		$this->functionCallExpression->analyse(new AnalyserContext($this->programRegistry, new VariableScope([
			'a' => $this->typeRegistry->function(
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			),
			'b' => $this->typeRegistry->boolean
		])));
	}

	public function testAnalyseFailWrongType(): void {
		$this->expectException(AnalyserException::class);
		$this->functionCallExpression->analyse(new AnalyserContext($this->programRegistry, new VariableScope([
			'a' => $this->typeRegistry->integer(),
			'b' => $this->typeRegistry->integer()
		])));
	}
	public function testExecuteDefault(): void {
		$result = $this->functionCallExpression->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([
			'a' => TypedValue::forValue($this->functionValue),
			'b' => TypedValue::forValue(
				$this->valueRegistry->integer(1)
			)
		])));
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteOnSubtypes(): void {
		$result = $this->functionCallExpression->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([
			'a' => TypedValue::forValue(
				$this->valueRegistry->subtypeValue(
					new TypeNameIdentifier('MyFunction'),
					$this->functionValue
				)
			),
			'b' => TypedValue::forValue(
				$this->valueRegistry->integer(1)
			)
		])));
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteOnCustomType(): void {
		$result = $this->functionCallExpression->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([
			'a' => TypedValue::forValue(
				$this->valueRegistry->sealedValue(
					new TypeNameIdentifier('MyCustomType'),
					$this->valueRegistry->record(['x' => $this->valueRegistry->integer(1)])
				)
			),
			'b' => TypedValue::forValue(
				$this->valueRegistry->integer(1)
			)
		])));
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteFailWrongType(): void {
		$this->expectException(ExecutionException::class);
		$this->functionCallExpression->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([
			'a' => TypedValue::forValue(
				$this->valueRegistry->integer(1)
			),
			'b' => TypedValue::forValue(
				$this->valueRegistry->integer(1)
			)
		])));
	}
}