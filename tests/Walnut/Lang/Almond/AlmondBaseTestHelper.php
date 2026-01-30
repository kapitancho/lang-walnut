<?php

namespace Walnut\Lang\Test\Almond;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandTypeBuilder;
use Walnut\Lang\Almond\Engine\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Registry\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Validation\ValidationFactory;

abstract class AlmondBaseTestHelper extends TestCase {

	protected readonly ProgramContext $programContext;
	protected readonly TypeRegistry $typeRegistry;
	protected readonly UserlandTypeBuilder $userlandTypeBuilder;
	protected readonly ValueRegistry $valueRegistry;
	protected readonly ExpressionRegistry $expressionRegistry;
	protected readonly ValidationFactory $validationFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->programContext = new ProgramContextFactory()->newProgramContext();
		$this->typeRegistry = $this->programContext->typeRegistry;
		$this->userlandTypeBuilder = $this->programContext->userlandTypeBuilder;
		$this->valueRegistry = $this->programContext->valueRegistry;
		$this->expressionRegistry = $this->programContext->expressionRegistry;
		$this->validationFactory = $this->programContext->validationFactory;
	}

}