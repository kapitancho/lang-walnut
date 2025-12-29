<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseOrTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseAnd(): void {
		$result = $this->executeCodeSnippet("{`Real<2..5.14>} | `Real<1..3>;");
		$this->assertEquals("type{Real<1..5.14>}", $result);
	}
	public function testIsSubtypeOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "{`Integer} | 3.14;");
	}

}