<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsRealOk(): void {
		$result = $this->executeCodeSnippet("'3.14'->asReal;");
		$this->assertEquals("3.14", $result);
	}

	public function testAsRealOkInteger(): void {
		$result = $this->executeCodeSnippet("'12'->asReal;");
		$this->assertEquals("12", $result);
	}

	public function testAsRealInvalidInteger(): void {
		$result = $this->executeCodeSnippet("'12 days'->asReal;");
		$this->assertEquals("@NotANumber", $result);
	}

}