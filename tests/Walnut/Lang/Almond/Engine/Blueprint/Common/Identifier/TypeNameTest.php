<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

final class TypeNameTest extends TestCase {

	public function testValidTypeName(): void {
		$this->assertEquals('Abc',
			new TypeName('Abc')->identifier
		);
		$this->assertTrue(
			new TypeName('Abc')->equals(new TypeName('Abc'))
		);
	}

	public function testInvalidTypeName(): void {
		$this->expectExceptionMessage('Invalid type name identifier: abc');
		new TypeName('abc');
	}

}