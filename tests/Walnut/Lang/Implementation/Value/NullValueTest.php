<?php

namespace Walnut\Lang\Test\Implementation\Value;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class NullValueTest extends TestCase {

	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
	}
	public function testNullValue(): void {
		$nullType = $this->typeRegistry->null();
		$nullValue = $this->valueRegistry->null();

		self::assertEquals($nullType, $nullValue->type());
		self::assertNull($nullValue->literalValue());
		self::assertTrue($nullValue->equals($nullType->value()));
		self::assertFalse($nullValue->equals($this->valueRegistry->true()));
	}
}