<?php

namespace Walnut\Lang\Test\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\IdentifierException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;

final class MethodNameIdentifierTest extends TestCase {
	public function testMethodNameIdentifier(): void {
		$methodNameIdentifier1 = new MethodNameIdentifier('MyIdentifier');
		$methodNameIdentifier2 = new MethodNameIdentifier('MyIdentifier');
		$methodNameIdentifier3 = new MethodNameIdentifier('MyOtherIdentifier');
		$this->assertTrue($methodNameIdentifier1->equals($methodNameIdentifier2));
		$this->assertFalse($methodNameIdentifier1->equals($methodNameIdentifier3));
		$this->assertEquals('MyIdentifier', $methodNameIdentifier1->identifier);
		$this->assertEquals('MyIdentifier', (string)$methodNameIdentifier1);
		$this->assertEquals('"MyIdentifier"', json_encode($methodNameIdentifier1));
	}

	public function testInvalidMethodNameIdentifier(): void {
		$this->expectException(IdentifierException::class);
		new MethodNameIdentifier('MyIdenti@fier');
	}
}
