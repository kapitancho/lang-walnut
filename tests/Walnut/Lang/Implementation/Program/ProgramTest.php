<?php

namespace Walnut\Lang\Test\Implementation\Program;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\BaseProgramTestHelper;

final class ProgramTest extends BaseProgramTestHelper {

	public function setUp(): void {
		parent::setUp();
	}

	private function doCall(): Value {
		return $this->program->getEntryPoint(
			new TypeNameIdentifier('CliEntryPoint')
		)->call($this->valueRegistry->null);
	}

	public function testExecuteOk(): void {
		$this->addCliEntryPoint(
			$this->valueRegistry->function(
				$this->typeRegistry->array(
					$this->typeRegistry->string()
				),
				null,
				$this->typeRegistry->nothing,
				$this->typeRegistry->string(),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$this->valueRegistry->string('42')
					)
				)
			)
		);
		$result = $this->program->getEntryPoint(
			new TypeNameIdentifier('CliEntryPoint')
		)->call($this->valueRegistry->tuple([$this->valueRegistry->string('')]));
		$this->assertEquals('42', (string)$result->literalValue);
	}

	public function testErrorTypeIsNotDefined(): void {
		try {
			$this->program->getEntryPoint(
				new TypeNameIdentifier('UndefinedType')
			);
		} catch (InvalidEntryPointDependency $e) {
			$this->assertEquals("type is not defined", $e->failReason);
		}
	}

	public function testErrorDependencyCannotBeResolved(): void {
		try {
			$this->doCall();
		} catch (InvalidEntryPointDependency $e) {
			$this->assertEquals("dependency cannot be resolved", $e->failReason);
		}
	}

	public function testErrorValueIsNotAFunction(): void {
		try {
			$this->typeRegistryBuilder->addData(
				new TypeNameIdentifier('MyInt'),
				$this->typeRegistry->integer()
			);
			$this->programContext->customMethodRegistryBuilder->addMethod(
				$this->typeRegistry->typeByName(new TypeNameIdentifier('DependencyContainer')),
				new MethodNameIdentifier('asMyInt'),
				$this->typeRegistry->null,
				null,
				$this->typeRegistry->nothing,
				$this->typeRegistry->typeByName(new TypeNameIdentifier('MyInt')),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$this->valueRegistry->dataValue(
							new TypeNameIdentifier('MyInt'),
							$this->valueRegistry->integer(42)
						)
					)
				),
			);
			$this->program->getEntryPoint(
				new TypeNameIdentifier('MyInt')
			)->call($this->valueRegistry->null);
		} catch (InvalidEntryPointDependency $e) {
			$this->assertEquals("value is not a function", $e->failReason);
		}
	}
}