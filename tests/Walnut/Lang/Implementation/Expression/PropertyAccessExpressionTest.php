<?php

namespace Walnut\Lang\Implementation\Expression;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
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
		$this->typeRegistry->addSubtype(
			new TypeNameIdentifier("MyRecord"),
			$this->typeRegistry->record([
				'x' => $this->typeRegistry->integer(),
				'y' => $this->typeRegistry->string(),
			]),
		);
		$this->typeRegistry->addSubtype(
			new TypeNameIdentifier("MyTuple"),
			$this->typeRegistry->tuple([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			]),
		);
	}
	
	public function testAnalyseDefault(): void {
		$result = $this->recordPropertyAccessExpression->analyse(
			new AnalyserContext(
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
			$result->expressionType()
		);

		$result = $this->tuplePropertyAccessExpression->analyse(
			new AnalyserContext(
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
			$result->expressionType()
		);
	}

	public function testAnalyseOnSubtypes(): void {
		$result = $this->recordPropertyAccessExpression->analyse(
			new AnalyserContext(
				new VariableScope([
					'#' => $this->typeRegistry->withName(new TypeNameIdentifier('MyRecord'))
				]
			)
		));
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType()
		);

		$result = $this->tuplePropertyAccessExpression->analyse(
			new AnalyserContext(
				new VariableScope([
					'#' => $this->typeRegistry->withName(new TypeNameIdentifier('MyTuple'))
				]
			)
		));
		self::assertEquals(
			$this->typeRegistry->string(),
			$result->expressionType()
		);
	}

	/*
	public function testAnalyseFailWrongRecordProperty(): void {
		$this->expectException(AnalyserException::class);
		$this->recordPropertyAccessExpression->analyse(
			new AnalyserContext(
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
			new AnalyserContext(
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
			new AnalyserContext(
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
			new AnalyserContext(
				new VariableScope([
					'#' => $this->typeRegistry->integer()
				])
			)
		);
	}

	public function testExecuteDefault(): void {
		$result = $this->recordPropertyAccessExpression->execute(
			new ExecutionContext(
				new VariableValueScope([
					'#' => TypedValue::forValue(
						$this->valueRegistry->record([
							'x' => $this->valueRegistry->integer(1),
							'y' => $this->valueRegistry->string("hi"),
						])
					)
				])
			)
		);
		self::assertTrue($result->value()->equals($this->valueRegistry->string("hi")));

		$result = $this->tuplePropertyAccessExpression->execute(
			new ExecutionContext(
				new VariableValueScope([
					'#' => TypedValue::forValue(
						$this->valueRegistry->tuple([
							$this->valueRegistry->integer(1),
							$this->valueRegistry->string("hi"),
						])
					)
				])
			)
		);
		self::assertTrue($result->value()->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteOnSubtypes(): void {
		$result = $this->recordPropertyAccessExpression->execute(
			new ExecutionContext(
				new VariableValueScope([
					'#' => TypedValue::forValue(
						$this->valueRegistry->subtypeValue(
							new TypeNameIdentifier('MyRecord'),
							$this->valueRegistry->record([
								'x' => $this->valueRegistry->integer(1),
								'y' => $this->valueRegistry->string("hi"),
							])
						)
					)
				])
			)
		);
		self::assertTrue($result->value()->equals($this->valueRegistry->string("hi")));

		$result = $this->tuplePropertyAccessExpression->execute(
			new ExecutionContext(
				new VariableValueScope([
					'#' => TypedValue::forValue(
						$this->valueRegistry->subtypeValue(
							new TypeNameIdentifier('MyTuple'),
							$this->valueRegistry->tuple([
								$this->valueRegistry->integer(1),
								$this->valueRegistry->string("hi"),
							])
						)
					)
				])
			)
		);
		self::assertTrue($result->value()->equals($this->valueRegistry->string("hi")));
	}

	public function testExecuteFailWrongType(): void {
		$this->expectException(ExecutionException::class);
		$this->recordPropertyAccessExpression->execute(
			new ExecutionContext(
				new VariableValueScope([
					'#' => TypedValue::forValue(
						$this->valueRegistry->integer(1)
					)
				])
			)
		);
	}

}