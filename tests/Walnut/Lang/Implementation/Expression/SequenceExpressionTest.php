<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\ReturnExpression;
use Walnut\Lang\Implementation\Code\Expression\SequenceExpression;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class SequenceExpressionTest extends TestCase {
	private readonly TypeRegistry $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly ProgramRegistry $programRegistry;
	private readonly SequenceExpression $sequenceExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->programRegistry = new ProgramContextFactory()->programContext->programRegistry;
		$this->typeRegistry = $this->programRegistry->typeRegistry;
		$this->valueRegistry = $this->programRegistry->valueRegistry;
		$this->sequenceExpression = new SequenceExpression(
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
		self::assertCount(2, $this->sequenceExpression->expressions);
	}

	public function testAnalyse(): void {
		$result = $this->sequenceExpression->analyse($this->programRegistry->analyserContext);
		self::assertTrue($result->expressionType->isSubtypeOf(
			$this->typeRegistry->string()
		));
	}

	public function testExecute(): void {
		$result = $this->sequenceExpression->execute(
			$this->programRegistry->executionContext
		);
		self::assertEquals(
			$this->valueRegistry->string("456"),
			$result->value
		);
	}

	public function testAnalyseWithReturn(): void {
		$result = new SequenceExpression(
			[
				new ReturnExpression(
					new ConstantExpression(
						$this->valueRegistry->integer(123)
					)
				),
				new ConstantExpression(
					$this->valueRegistry->string("456")
				)
			]
		)->analyse($this->programRegistry->analyserContext);
		self::assertTrue($result->expressionType->isSubtypeOf(
			$this->typeRegistry->integer()
		));
	}

}