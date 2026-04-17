<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;

final class EnumNameTest extends TestCase {

	public function testValidEnumerationValueName(): void {
		$this->assertEquals('Abc',
			new EnumerationValueName('Abc')->identifier
		);
		$this->assertTrue(
			new EnumerationValueName('Abc')->equals(new EnumerationValueName('Abc'))
		);
	}

	public function testInvalidEnumerationValueName(): void {
		$this->expectExceptionMessage('Invalid enum value identifier: Abc@d');
		new EnumerationValueName('Abc@d');
	}

}