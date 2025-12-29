<?php

namespace Walnut\Lang\Test\Implementation\Value;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\AST\Parser\BytesEscapeCharHandler;
use Walnut\Lang\Implementation\AST\Parser\StringEscapeCharHandler;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\NestedMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class BooleanValueTest extends TestCase {

	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder(
			new CustomMethodRegistryBuilder(),
			new MainMethodRegistry(
				new NativeCodeTypeMapper(),
				new NestedMethodRegistry(),
				[]
			),
			$ech = new StringEscapeCharHandler()
		);
		$this->valueRegistry = new ValueRegistry(
			$this->typeRegistry,
			$ech,
			new BytesEscapeCharHandler()
		);
	}
	public function testBooleanValue(): void {
		$trueType = $this->typeRegistry->true;
		$falseType = $this->typeRegistry->false;
		$trueValue = $this->valueRegistry->true;
		$falseValue = $this->valueRegistry->false;

		self::assertEquals($trueType, $trueValue->type);
		self::assertEquals($falseType, $falseValue->type);
		self::assertTrue($trueValue->literalValue);
		self::assertFalse($falseValue->literalValue);
		self::assertTrue($trueValue->equals($trueType->value));
		self::assertFalse($trueValue->equals($falseType->value));
	}
}