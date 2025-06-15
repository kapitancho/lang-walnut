<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsMutableOfTypeTest extends CodeExecutionTestHelper {

	public function testAsMutableOfTypeError(): void {
		$result = $this->executeCodeSnippet("3.14->asMutableOfType(type{Integer});");
		$this->assertEquals("@CastNotAvailable!!!!![\n\tfrom: type{Real[3.14]},\n\tto: type{Integer}\n]", $result);
	}

	public function testAsMutableOfTypeOk(): void {
		$result = $this->executeCodeSnippet("3.14->asMutableOfType(type{Real});");
		$this->assertEquals("mutable{Real, 3.14}", $result);
	}

}