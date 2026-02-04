<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryBitwiseAndTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseAnd(): void {
		$result = $this->executeCodeSnippet("{`Real<2..5.14>} & `Integer<1..3>;");
		$this->assertEquals("type{Integer<2..3>}", $result);
	}
	public function testIsSubtypeOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "{`Integer} & 3.14;");
	}

}