<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\TupleExpression;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class TupleExpressionTest extends TestCase {
	private readonly TypeRegistry $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly ProgramRegistry $programRegistry;
	private readonly TupleExpression $tupleExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->programRegistry = new ProgramContextFactory()->programContext->programRegistry;
		$this->typeRegistry = $this->programRegistry->typeRegistry;
		$this->valueRegistry = $this->programRegistry->valueRegistry;
		$this->tupleExpression = new TupleExpression(
			[
				new ConstantExpression(
					$this->valueRegistry->integer(123)
				),
				new ConstantExpression(
					$this->valueRegistry->string("456")
				)
			]
		);
	}

	public function testValues(): void {
		self::assertCount(2, $this->tupleExpression->values);
	}

	public function testAnalyse(): void {
		$result = $this->tupleExpression->analyse($this->programRegistry->analyserContext);
		self::assertTrue($result->expressionType->isSubtypeOf(
			$this->typeRegistry->tuple([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string()
			])
		));
	}

	public function testExecute(): void {
		$result = $this->tupleExpression->execute($this->programRegistry->executionContext);
		self::assertEquals(
			(string)$this->valueRegistry->tuple([
				$this->valueRegistry->integer(123),
				$this->valueRegistry->string("456")
			]),
			(string)$result->value
		);
	}

}