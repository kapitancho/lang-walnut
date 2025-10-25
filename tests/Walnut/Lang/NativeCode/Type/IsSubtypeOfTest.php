<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class IsSubtypeOfTest extends CodeExecutionTestHelper {

	public function testIsSubtypeOf(): void {
		$result = $this->executeCodeSnippet("type{Integer}->isSubtypeOf(type{Real});");
		$this->assertEquals("true", $result);
	}

	public function testIsSubtypeOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "type{Integer}->isSubtypeOf(3.14);");
	}

}