<?php

namespace Walnut\Lang\Test\Implementation\Type;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;

final class NullTypeTest extends TestCase {

	private readonly TypeRegistryBuilder $typeRegistry;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
	}
	public function testNullType(): void {
		$nullType = $this->typeRegistry->null;
		self::assertEquals('Null', $nullType->name->identifier);
		self::assertNull($nullType->value->literalValue);
		self::assertTrue($nullType->isSubtypeOf($this->typeRegistry->null));
		self::assertFalse($nullType->isSubtypeOf($this->typeRegistry->boolean));
	}
}