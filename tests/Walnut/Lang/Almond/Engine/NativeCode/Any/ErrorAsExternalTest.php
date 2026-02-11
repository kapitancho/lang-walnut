<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ErrorAsExternalTest extends CodeExecutionTestHelper {

	public function testErrorAsExternalOk(): void {
		$result = $this->executeCodeSnippet("{5}->errorAsExternal('error');");
		$this->assertEquals("5", $result);
	}

	public function testErrorAsExternalErrorString(): void {
		$result = $this->executeCodeSnippet("{@5}->errorAsExternal('error');");
		$this->assertEquals("@ExternalError[\n\terrorType: 'Integer[5]',\n\toriginalError: @5,\n\terrorMessage: 'error'\n]", $result);
	}

	public function testErrorAsExternalErrorNull(): void {
		$result = $this->executeCodeSnippet("{@5}->errorAsExternal;");
		$this->assertEquals("@ExternalError[\n\terrorType: 'Integer[5]',\n\toriginalError: @5,\n\terrorMessage: 'Error'\n]", $result);
	}

	public function testErrorAsExternalInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "{5}->errorAsExternal(42);");
	}
}