<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Program;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Engine\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Program\Validation\NoopValidationResultCollector;

class ProgramTest extends TestCase {

	private ProgramContext $programContext;

	protected function setUp(): void {
		parent::setUp();
		
		$this->programContext =
			new ProgramContextFactory()->newProgramContext(
				new NoopValidationResultCollector()
			);
		$n = new TypeName('DependencyContainer');
		$this->programContext->userlandTypeStorage->addAtom(
			$n,
			$this->programContext->userlandTypeFactory->atom($n)
		);
	}

	public function testTypeAlreadyDefined(): void {
		$this->expectExceptionMessage('The type with the name "DependencyContainer" is already defined and cannot be re-defined.');

		$n = new TypeName('DependencyContainer');
		$this->programContext->userlandTypeStorage->addAtom(
			$n,
			$this->programContext->userlandTypeFactory->atom($n)
		);
	}

	public function testErrorMissingType(): void {
		$this->expectExceptionMessage('type MissingType: type is not defined');
		$this->programContext->validateAndBuildProgram()
			->getEntryPoint(new TypeName('MissingType'));
	}

	public function testErrorDependencyCannotBeResolved(): void {
		$this->expectExceptionMessage('dependency cannot be resolved');
		$n = new TypeName('MyEntryPoint');
		$this->programContext->userlandTypeStorage->addAlias(
			$n,
			$this->programContext->userlandTypeFactory->alias(
				$n,
				$this->programContext->typeRegistry->function(
					$this->programContext->typeRegistry->any,
					$this->programContext->typeRegistry->any,
				)
			)
		);
		$this->programContext->validateAndBuildProgram()
			->getEntryPoint($n);
	}

	public function testErrorValueIsNotAFunction(): void {
		$this->expectExceptionMessage('value is not a function');
		$this->programContext->validateAndBuildProgram()
			->getEntryPoint(new TypeName('Null'));
	}

}