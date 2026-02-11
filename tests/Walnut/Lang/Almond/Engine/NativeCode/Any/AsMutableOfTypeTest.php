<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsMutableOfTypeTest extends CodeExecutionTestHelper {

	public function testAsMutableOfTypeError(): void {
		$result = $this->executeCodeSnippet("3.14->asMutableOfType(type{Integer});");
		$this->assertEquals("@CastNotAvailable![\n\tfrom: type{Real[3.14]},\n\tto: type{Integer}\n]", $result);
	}

	public function testAsMutableOfTypeOk(): void {
		$result = $this->executeCodeSnippet("3.14->asMutableOfType(type{Real});");
		$this->assertEquals("mutable{Real, 3.14}", $result);
	}

	public function testAsMutableOfTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"3.14->asMutableOfType(42);"
		);
	}

}