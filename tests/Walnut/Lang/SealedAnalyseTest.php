<?php

namespace Walnut\Lang;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\ProgramEntryPoint;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Test\BaseProgramTestHelper;

final class SealedAnalyseTest extends BaseProgramTestHelper {

	protected function addCoreToContext(): void {}

	private function getSealedType(): void {
		$mySealed = new TypeNameIdentifier('MySealed');
		$this->typeRegistryBuilder->addSealed(
			$mySealed,
			$this->typeRegistry->record([
				'x' => $this->typeRegistry->integer(),
				'y' => $this->typeRegistry->string()
			]),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->sequence([
					$this->expressionRegistry->constant(
						$this->valueRegistry->true
					),
					$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
				])
			),
			null
		);
	}

	private function getEntryPointFor(Type $r, Type $p = null): ProgramEntryPoint {
		$p ??= $this->typeRegistry->record([
			'x' => $this->typeRegistry->integer(),
			'y' => $this->typeRegistry->string()
		]);
		$fn = new VariableNameIdentifier('fn');
		$this->globalScopeBuilder->addVariable(
			$fn,
			$t=$this->valueRegistry->function(
				$p ?? $this->typeRegistry->integer(),
				$this->typeRegistry->nothing,
				$r,
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->methodCall(
						$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
						new MethodNameIdentifier('construct'),
						$this->expressionRegistry->constant(
							$this->valueRegistry->type($r)
						)
					)
				)
			)
		);
		return $this->programContext->analyseAndBuildProgram()->getEntryPoint(
			new VariableNameIdentifier('fn'),
			$p ?? $this->typeRegistry->integer(),
			$r
		);
	}

	public function testBasicValidSubtype(): void {
		$this->getSealedType();
		$result = $this->getEntryPointFor(
			$this->typeRegistry->sealed(new TypeNameIdentifier('MySealed')),
		)->call($this->valueRegistry->record([
			'x' => $this->valueRegistry->integer(10),
			'y' => $this->valueRegistry->string('hello')
		]));
		$this->assertEquals("MySealed[x: 10, y: 'hello']", (string)$result);
	}

	/*
	public function testBasicValidSubtypeWithDeclaredErrorType(): void {
		$this->getSubtype(
			$this->expressionRegistry->constant($this->valueRegistry->null()),
			$this->typeRegistry->string()
		);
		$result = $this->getEntryPointFor(
			$this->typeRegistry->result(
				$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype')),
				$this->typeRegistry->string()
			)
		)->call($this->valueRegistry->integer(10));
		$this->assertEquals('MySubtype{10}', (string)$result);
	}

	public function testBasicValidSubtypeWithReturnedErrorType(): void {
		$this->getSubtype(
			$this->expressionRegistry->return(
				$this->expressionRegistry->constant(
					$this->valueRegistry->error(
						$this->valueRegistry->string('error'),
					)
				)
			),
			$this->typeRegistry->string()
		);
		$result = $this->getEntryPointFor(
			$this->typeRegistry->result(
				$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype')),
				$this->typeRegistry->string()
			)
		)->call($this->valueRegistry->integer(10));
		$this->assertEquals("@'error'", (string)$result);
	}

	public function testUndeclaredErrorType(): void {
		$this->getSubtype(
			$this->expressionRegistry->return(
				$this->expressionRegistry->constant(
					$this->valueRegistry->error(
						$this->valueRegistry->string('error'),
					)
				)
			),
			$this->typeRegistry->nothing()
		);
		$this->expectException(AnalyserException::class);
		$this->getEntryPointFor(
			$this->typeRegistry->result(
				$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype')),
				$this->typeRegistry->string()
			)
		);
	}

	public function testDifferentErrorType(): void {
		$this->getSubtype(
			$this->expressionRegistry->return(
				$this->expressionRegistry->constant(
					$this->valueRegistry->error(
						$this->valueRegistry->string('error'),
					)
				)
			),
			$this->typeRegistry->boolean()
		);
		$this->expectException(AnalyserException::class);
		$this->getEntryPointFor(
			$this->typeRegistry->result(
				$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype')),
				$this->typeRegistry->string()
			)
		);
	}

	public function testInvalidBaseValue(): void {
		$this->getSubtype(
			$this->expressionRegistry->constant($this->valueRegistry->null()),
			$this->typeRegistry->nothing()
		);
		$this->expectException(AnalyserException::class);
		$this->getEntryPointFor(
			$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype')),
			$this->typeRegistry->real()
		)->call($this->valueRegistry->real(3.14));
	}
	*/
}