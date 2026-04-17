<?php

namespace Walnut\Lang\Test\Almond\Engine\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;

final class VariableNameTest extends TestCase {

	public function testValidVariableName(): void {
		$this->assertEquals('abc',
			new VariableName('abc')->identifier
		);
		$this->assertTrue(
			new VariableName('abc')->equals(new VariableName('abc'))
		);
	}

	public function testInvalidVariableName(): void {
		$this->expectExceptionMessage('Invalid variable name identifier: Abc');
		new VariableName('Abc');
	}

}