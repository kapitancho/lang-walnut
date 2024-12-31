<?php

namespace Walnut\Lang;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\ProgramEntryPoint;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Test\BaseProgramTestHelper;

final class SubtypeAnalyseTest extends BaseProgramTestHelper {

	private function getSubtype(Expression $bodyExpression, Type $errorType): void {
		$mySubtype = new TypeNameIdentifier('MySubtype');
		$this->programBuilder->addSubtype(
			$mySubtype,
			$this->typeRegistry->integer(),
			$bodyExpression,
			$errorType
		);
	}

	private function getEntryPointFor(Type $r, Type|null $p = null): ProgramEntryPoint {
		$fn = new VariableNameIdentifier('fn');
		$this->programBuilder->addVariable(
			$fn,
			$this->valueRegistry->function(
				$p ?? $this->typeRegistry->integer(),
				$this->typeRegistry->nothing,
				$r,
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->methodCall(
						$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
						new MethodNameIdentifier('construct'),
						$this->expressionRegistry->constant(
							$this->valueRegistry->type(
								$r instanceof ResultType ? $r->returnType : $r
							)
						)
					)
				)
			)
		);
		return $this->programBuilder->analyseAndBuildProgram()->getEntryPoint(
			new VariableNameIdentifier('fn'),
			$p ?? $this->typeRegistry->integer(),
			$r
		);
	}

	public function testBasicValidSubtype(): void {
		$this->getSubtype(
			$this->expressionRegistry->constant($this->valueRegistry->null),
			$this->typeRegistry->nothing
		);
		$result = $this->getEntryPointFor(
			$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype')),
		)->call($this->valueRegistry->integer(10));
		$this->assertEquals('MySubtype{10}', (string)$result);
	}

	public function testBasicValidSubtypeWithDeclaredErrorType(): void {
		$this->getSubtype(
			$this->expressionRegistry->constant($this->valueRegistry->null),
			$this->typeRegistry->string()
		);
		$result = $this->getEntryPointFor(
			$this->typeRegistry->result(
				$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype')),
				$this->typeRegistry->string()
			),
			$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype'))
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
			$this->typeRegistry->nothing
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
			$this->typeRegistry->boolean
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
			$this->expressionRegistry->constant($this->valueRegistry->null),
			$this->typeRegistry->nothing
		);
		$this->expectException(AnalyserException::class);
		$this->getEntryPointFor(
			$this->typeRegistry->subtype(new TypeNameIdentifier('MySubtype')),
			$this->typeRegistry->real()
		)->call($this->valueRegistry->real(3.14));
	}

}