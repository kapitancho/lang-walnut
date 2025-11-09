<?php

namespace Walnut\Lang\Test\NativeCode\PasswordString;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class HashVerifyTest extends CodeExecutionTestHelper {

	public function testHashVerifyTrue(): void {
		$result = $this->executeCodeSnippet("x = PasswordString['test-123']; x->verify(x->hash);");
		$this->assertEquals("true", $result);
	}

	public function testHashVerifyFalse(): void {
		$result = $this->executeCodeSnippet("x = PasswordString['test-123']; x->verify('wrong');");
		$this->assertEquals("false", $result);
	}

	public function testHashVerifyInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"x = PasswordString['test-123']; x->verify(42);"
		);
	}

}