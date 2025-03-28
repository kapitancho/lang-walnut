<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class PropertyAccessExpressionTest extends BaseProgramTestHelper {

	private MethodCallExpression $recordPropertyAccessExpression;
	private MethodCallExpression $tuplePropertyAccessExpression;

	private function propertyAccess(Expression $target, int|string $propertyName): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier('item'),
			$this->expressionRegistry->constant(
				is_int($propertyName) ?
					$this->valueRegistry->integer($propertyName) :
					$this->valueRegistry->string($propertyName)
			)
		);
	}

	protected function setUp(): void {
		parent::setUp();
		$this->recordPropertyAccessExpression = $this->propertyAccess(
			$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
			'y'
		);
		$this->tuplePropertyAccessExpression = $this->propertyAccess(
			$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
			1
		);
		$this->typeRegistry->addAlias(
			new TypeNameIdentifier("MyRecord"),
			$this->typeRegistry->record([
				'x' => $this->typeRegistry->integer(),
				'y' => $this->typeRegistry->string(),
			]),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->null
				)
			),
			$this->typeRegistry->nothing
		);
		$this->typeRegistry->addAlias(
			new TypeNameIdentifier("MyTuple"),
			$this->typeRegistry->tuple([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			]),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->null
				)
			),
			$this->typeRegistry->nothing
		);
	}
	
	public function testAnalyseDefault(): void {
		$result = $this->recordPropertyAccessExpression->analyse(
			new AnalyserContext($this->programRegistry,
				new VariableScope([
					'#' => $this->typeRegistry->record([
						'x' => $this->typeRegistry->integer(),
						'y' => $this->typeRegistry->string(),
					])
				])
			)
		);
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType
		);

		$result = $this->tuplePropertyAccessExpression->analyse(
			new AnalyserContext($this->programRegistry,
				new VariableScope([
					'#' => $this->typeRegistry->tuple([
						$this->typeRegistry->integer(),
						$this->typeRegistry->string(),
					])
				])
			)
		);
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType
		);
	}

	public function testAnalyseOnSubtypes(): void {
		$result = $this->recordPropertyAccessExpression->analyse(
			new AnalyserContext($this->programRegistry,
				new VariableScope([
					'#' => $this->typeRegistry->withName(new TypeNameIdentifier('MyRecord'))
				]
			)
		));
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType
		);

		$result = $this->tuplePropertyAccessExpression->analyse(
			new AnalyserContext($this->programRegistry,
				new VariableScope([
					'#' => $this->typeRegistry->withName(new TypeNameIdentifier('MyTuple'))
				]
			)
		));
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType
		);
	}

	/*
	public function testAnalyseFailWrongRecordProperty(): void {
		$this->expectException(AnalyserException::class);
		$this->recordPropertyAccessExpression->analyse(
			new AnalyserContext($this->programRegistry,
				new VariableScope([
					'#' => $this->typeRegistry->record([
						'a' => $this->typeRegistry->integer(),
						'b' => $this->typeRegistry->string(),
					])
				])
			)
		);
	}

	public function testAnalyseFailWrongTupleProperty(): void {
		$this->expectException(AnalyserException::class);
		$this->tuplePropertyAccessExpression->analyse(
			new AnalyserContext($this->programRegistry,
				new VariableScope([
					'#' => $this->typeRegistry->tuple([
						$this->typeRegistry->integer(),
					])
				])
			)
		);
	}
	*/

	public function testAnalyseFailStringPropertyOnTuple(): void {
		$this->expectException(AnalyserException::class);
		$this->recordPropertyAccessExpression->analyse(
			new AnalyserContext($this->programRegistry,
				new VariableScope([
					'#' => $this->typeRegistry->tuple([
						$this->typeRegistry->integer(),
						$this->typeRegistry->string(),
					])
				])
			)
		);
	}

	public function testAnalyseFailWrongType(): void {
		$this->expectException(AnalyserException::class);
		$this->recordPropertyAccessExpression->analyse(
			new AnalyserContext($this->programRegistry,
				new VariableScope([
					'#' => $this->typeRegistry->integer()
				])
			)
		);
	}

	public function testExecuteDefault(): void {
		$result = $this->recordPropertyAccessExpression->execute(
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([
					'#' => (
						$this->valueRegistry->record([
							'x' => $this->valueRegistry->integer(1),
							'y' => $this->valueRegistry->string("hi"),
						])
					)
				])
			)
		);
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));

		$result = $this->tuplePropertyAccessExpression->execute(
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([
					'#' => (
						$this->valueRegistry->tuple([
							$this->valueRegistry->integer(1),
							$this->valueRegistry->string("hi"),
						])
					)
				])
			)
		);
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteOnSubtypes(): void {
		$result = $this->recordPropertyAccessExpression->execute(
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([
					'#' => (
						$this->valueRegistry->record([
							'x' => $this->valueRegistry->integer(1),
							'y' => $this->valueRegistry->string("hi"),
						])
					)
				])
			)
		);
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));

		$result = $this->tuplePropertyAccessExpression->execute(
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([
					'#' => (
						$this->valueRegistry->tuple([
							$this->valueRegistry->integer(1),
							$this->valueRegistry->string("hi"),
						])
					)
				])
			)
		);
		self::assertTrue($result->value->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteFailWrongType(): void {
		$this->expectException(ExecutionException::class);
		$this->recordPropertyAccessExpression->execute(
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([
					'#' => (
						$this->valueRegistry->integer(1)
					)
				])
			)
		);
	}

}