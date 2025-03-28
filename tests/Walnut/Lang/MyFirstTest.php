<?php

namespace Walnut\Lang\Test;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final class MyFirstTest extends BaseProgramTestHelper {

	protected function addCoreToContext(): void {}

	public function testBasicProgram(): void {
		$myFirstType = new TypeNameIdentifier('MyFirstType');
		$this->typeRegistryBuilder->addAlias(
			$myFirstType,
			$this->typeRegistry->tuple([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string()
			])
		);
		$x = new VariableNameIdentifier('x');
		$this->globalScopeBuilder->addVariable(
			$x,
			$this->valueRegistry->integer(10)
		);
		$fn = new VariableNameIdentifier('fn');
		$this->globalScopeBuilder->addVariable(
			$fn,
			$this->valueRegistry->function(
				$this->typeRegistry->string(),
				null,
				$this->typeRegistry->nothing,
				$this->typeRegistry->alias($myFirstType),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->tuple([
						$this->expressionRegistry->variableName($x),
						$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
					])
				)
			)
		);
		$program = $this->programContext->analyseAndBuildProgram();
		$entryPoint = $program->getEntryPoint(
			$fn,
			$this->typeRegistry->string(),
			$this->typeRegistry->alias($myFirstType)
		);
		$this->assertEquals("[10, 'Hello']", (string)$entryPoint->call(
			$this->valueRegistry->string('Hello')
		));
	}

	public function testBasicDependency(): void {
		$atomName = new TypeNameIdentifier('MyFirstAtom');
		$atomType = $this->typeRegistryBuilder->addAtom($atomName);
		$fn = new VariableNameIdentifier('fn');
		$this->globalScopeBuilder->addVariable(
			$fn,
			$this->valueRegistry->function(
				$this->typeRegistry->boolean,
				null,
				$atomType,
				$this->typeRegistry->tuple([
					$this->typeRegistry->boolean,
					$atomType
				]),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->tuple([
						$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
						$this->expressionRegistry->variableName(new VariableNameIdentifier('%')),
					])
				)
			)
		);
		$program = $this->programContext->analyseAndBuildProgram();
		$entryPoint = $program->getEntryPoint(
			$fn,
			$this->typeRegistry->boolean,
			$this->typeRegistry->array()
		);
		$this->assertEquals("[true, MyFirstAtom()]", (string)$entryPoint->call(
			$this->valueRegistry->true
		));
	}

	public function testBasicMethodCall(): void {
		$f = new VariableNameIdentifier('f');
		$this->globalScopeBuilder->addVariable(
			$f,
			$this->valueRegistry->function(
				$this->typeRegistry->string(),
				null,
				$this->typeRegistry->nothing,
				$this->typeRegistry->tuple([
					$this->typeRegistry->string()
				]),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->tuple([
						$this->expressionRegistry->variableName(new VariableNameIdentifier('#')),
					])
				)
			)
		);
		$fn = new VariableNameIdentifier('fn');
		$this->globalScopeBuilder->addVariable(
			$fn,
			$this->valueRegistry->function(
				$this->typeRegistry->string(),
				null,
				$this->typeRegistry->nothing,
				$this->typeRegistry->tuple([
					$this->typeRegistry->string()
				]),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->methodCall(
						$this->expressionRegistry->variableName(new VariableNameIdentifier('f')),
						new MethodNameIdentifier('invoke'),
						$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
					)
				)
			)
		);
		$program = $this->programContext->analyseAndBuildProgram();
		$entryPoint = $program->getEntryPoint(
			$fn,
			$this->typeRegistry->string(),
			$this->typeRegistry->array()
		);
		$this->assertEquals("['hello']", (string)$entryPoint->call(
			$this->valueRegistry->string('hello')
		));
	}
}