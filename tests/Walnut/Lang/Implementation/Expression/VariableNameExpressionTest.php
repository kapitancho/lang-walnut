<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\AST\Parser\BytesEscapeCharHandler;
use Walnut\Lang\Implementation\AST\Parser\StringEscapeCharHandler;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\VariableNameExpression;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\NestedMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class VariableNameExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly VariableNameExpression $variableNameExpression;
	private readonly ProgramRegistry $programRegistry;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder(
			new CustomMethodRegistryBuilder(),
			new MainMethodRegistry(
				new NativeCodeTypeMapper(),
				new NestedMethodRegistry(),
				[]
			),
			$ech = new StringEscapeCharHandler()
		);
		$this->valueRegistry = new ValueRegistry(
			$this->typeRegistry,
			$ech,
			new BytesEscapeCharHandler()
		);
		$this->variableNameExpression = new VariableNameExpression(
			new VariableNameIdentifier('x')
		);
		$this->programRegistry = new ProgramContextFactory()->programContext->programRegistry;
	}

	public function testVariableName(): void {
		self::assertEquals('x',
			$this->variableNameExpression->variableName->identifier);
	}

	public function testAnalyse(): void {
		$result = $this->variableNameExpression->analyse(
			new AnalyserContext($this->programRegistry, new VariableScope([
				'x' => $this->typeRegistry->integer()
			]))
		);
		self::assertEquals($this->typeRegistry->integer(), $result->expressionType);
	}

	public function testExecute(): void {
		$result = $this->variableNameExpression->execute(
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([
					'x' => ($this->valueRegistry->integer(123))
				])
			)
		);
		self::assertEquals($this->valueRegistry->integer(123), $result->value);
	}

}