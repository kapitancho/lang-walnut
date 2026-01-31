<?php

namespace Walnut\Lang\Test\Almond;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeBuilder;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Almond\Engine\Implementation\Program\Validation\ValidationFactory;

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


	protected function addSampleTypes(): void {
		$i = fn(string $name) => new TypeName($name);
		$ev = fn(string $name) => new EnumerationValueName($name);
		$this->userlandTypeBuilder->addAlias($i('MyAlias'), $this->typeRegistry->null);
		$this->userlandTypeBuilder->addAlias($i('NonEmptyString'), $this->typeRegistry->string(1));
		$this->userlandTypeBuilder->addAtom($i('MyAtom'));
		$this->userlandTypeBuilder->addEnumeration($i('MyEnum'), [
			$ev('A'),
			$ev('B'),
			$ev('C')
		]);
		$this->userlandTypeBuilder->addSealed($i('MySealed'), $this->typeRegistry->null, null);
		$this->userlandTypeBuilder->addOpen($i('MyOpen'), $this->typeRegistry->null, null);
		$this->userlandTypeBuilder->addData($i('MyData'), $this->typeRegistry->null);
	}

}