<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AppendTest extends CodeExecutionTestHelper {

	public function testAppend(): void {
		$result = $this->executeCodeSnippet("mutable{String, 'hello'}->APPEND(' world');");
		$this->assertEquals("mutable{String, 'hello world'}", $result);
	}

	public function testSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{String, 'hello'}->APPEND(5);");
	}

	public function testSetInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->APPEND('hello');");
	}

}