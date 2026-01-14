<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\NoExternalErrorExpression;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class NoExternalErrorExpressionTest extends TestCase {
	private readonly TypeRegistry $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly ProgramRegistry $programRegistry;
	private readonly NoExternalErrorExpression $noErrorExpression;
	private readonly NoExternalErrorExpression $errorExpression;

	protected function setUp(): void {
		parent::setUp();
		$programContext = new ProgramContextFactory()->programContext;
		$this->typeRegistry = $programContext->typeRegistry;
		$this->valueRegistry = $programContext->valueRegistry;
		$programContext->typeRegistryBuilder->addSealed(
			new TypeNameIdentifier('ExternalError'),
			$programContext->typeRegistry->record([
				'errorType' => $programContext->typeRegistry->string(),
				'originalError' => $programContext->typeRegistry->null,
				'errorMessage' => $programContext->typeRegistry->string(),
			])
		);
		$this->noErrorExpression = new NoExternalErrorExpression(
			new ConstantExpression(
				$this->valueRegistry->integer(123)
			),
		);
		$this->errorExpression = new NoExternalErrorExpression(
			new ConstantExpression(
				$this->valueRegistry->error(
					$this->valueRegistry->sealedValue(
						new TypeNameIdentifier('ExternalError'),
						$this->valueRegistry->record([
							'errorType' => $this->valueRegistry->string('Error'),
							'originalError' => $this->valueRegistry->null,
							'errorMessage' => $this->valueRegistry->string('Message'),
						])
					)
				)
			),
		);
		$this->programRegistry = $programContext->programRegistry;
	}

	public function testReturnedExpression(): void {
		self::assertInstanceOf(ConstantExpression::class,
			$this->noErrorExpression->targetExpression);
	}

	public function testAnalyse(): void {
		$result = $this->noErrorExpression->analyse($this->programRegistry->analyserContext);
		self::assertTrue($result->returnType()->isSubtypeOf(
			$this->typeRegistry->integer()
		));
	}

	public function testExecute(): void {
		$this->expectNotToPerformAssertions();
		$this->noErrorExpression->execute($this->programRegistry->executionContext);
	}

	public function testExecuteOnError(): void {
		$this->expectException(FunctionReturn::class);
		$this->errorExpression->execute($this->programRegistry->executionContext);
	}

	public function testExecuteResult(): void {
		try {
			$this->errorExpression->execute($this->programRegistry->executionContext);
		} catch (FunctionReturn $e) {
			self::assertEquals(
				$this->valueRegistry->error(
					$this->valueRegistry->sealedValue(
						new TypeNameIdentifier('ExternalError'),
						$this->valueRegistry->record([
							'errorType' => $this->valueRegistry->string('Error'),
							'originalError' => $this->valueRegistry->null,
							'errorMessage' => $this->valueRegistry->string('Message'),
						])
					)
				),
				$e->typedValue
			);
			return;
		}
	}

}