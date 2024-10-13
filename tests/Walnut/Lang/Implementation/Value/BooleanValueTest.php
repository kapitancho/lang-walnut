<?php

namespace Walnut\Lang\Test\Implementation\Value;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class BooleanValueTest extends TestCase {

	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
	}
	public function testBooleanValue(): void {
		$trueType = $this->typeRegistry->true();
		$falseType = $this->typeRegistry->false();
		$trueValue = $this->valueRegistry->true();
		$falseValue = $this->valueRegistry->false();

		self::assertEquals($trueType, $trueValue->type());
		self::assertEquals($falseType, $falseValue->type());
		self::assertTrue($trueValue->literalValue());
		self::assertFalse($falseValue->literalValue());
		self::assertTrue($trueValue->equals($trueType->value()));
		self::assertFalse($trueValue->equals($falseType->value()));
	}
}