<?php

namespace Walnut\Lang\Test\Implementation\Type;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\NestedMethodRegistry;

final class NullTypeTest extends TestCase {

	private readonly TypeRegistryBuilder $typeRegistry;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder(
			new CustomMethodRegistryBuilder(),
			new MainMethodRegistry(
				new NativeCodeTypeMapper(),
				new NestedMethodRegistry(),
				[]
			),
			new EscapeCharHandler()
		);
	}
	public function testNullType(): void {
		$nullType = $this->typeRegistry->null;
		self::assertEquals('Null', $nullType->name->identifier);
		self::assertNull($nullType->value->literalValue);
		self::assertTrue($nullType->isSubtypeOf($this->typeRegistry->null));
		self::assertFalse($nullType->isSubtypeOf($this->typeRegistry->boolean));
	}
}