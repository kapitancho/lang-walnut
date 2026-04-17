<?php

namespace Walnut\Lang\Test\Almond\Engine\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;

final class MethodNameTest extends TestCase {

	public function testValidMethodName(): void {
		$this->assertEquals('abc',
			new MethodName('abc')->identifier
		);
		$this->assertTrue(
			new MethodName('abc')->equals(new MethodName('abc'))
		);
	}

	public function testInvalidMethodName(): void {
		$this->expectExceptionMessage('Invalid method name identifier: abc@d');
		new MethodName('abc@d');
	}

}