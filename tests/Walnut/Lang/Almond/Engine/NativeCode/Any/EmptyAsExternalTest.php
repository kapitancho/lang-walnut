<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class EmptyAsExternalTest extends CodeExecutionTestHelper {

	public function testErrorAsExternalOk(): void {
		$result = $this->executeCodeSnippet("5 ?* ('error');");
		$this->assertEquals("5", $result);
	}

	public function testErrorAsExternalErrorString(): void {
		$result = $this->executeCodeSnippet("empty ?* ('error');");
		$this->assertEquals("@ExternalError[\n\terrorType: 'Empty',\n\terrorMessage: 'error'\n]", $result);
	}

	public function testErrorAsExternalErrorNull(): void {
		$result = $this->executeCodeSnippet("empty ?* (null);");
		$this->assertEquals("@ExternalError[\n\terrorType: 'Empty',\n\terrorMessage: 'Error'\n]", $result);
	}

	public function testErrorAsExternalInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "5 ?* (42);");
	}
}