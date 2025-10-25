<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FlipMapTest extends CodeExecutionTestHelper {

	public function testFlipMapEmpty(): void {
		$result = $this->executeCodeSnippet("[]->flipMap(^String => Integer :: #->length);");
		$this->assertEquals("[:]", $result);
	}

	public function testFlipMapNonEmpty(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef']->flipMap(^String => Integer :: #->length);");
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testFlipMapNonEmptyError(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef']->flipMap(^String => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testFlipMapInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 'a']->flipMap(^String => Integer :: #->length);");
	}

	public function testFlipMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['a', 'bcd', 'ef']->flipMap(5);");
	}

	public function testFlipMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type String['a', 'bcd', 'ef'] of the callback function is not a subtype of Boolean",
			"['a', 'bcd', 'ef']->flipMap(^Boolean => Boolean :: true);");
	}

}