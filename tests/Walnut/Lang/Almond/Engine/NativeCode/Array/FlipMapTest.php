<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FlipMapTest extends CodeExecutionTestHelper {

	public function testFlipMapEmpty(): void {
		$result = $this->executeCodeSnippet("[]->flipMap(^String => Integer :: #->length);");
		$this->assertEquals("[:]", $result);
	}

	public function testFlipMapNonEmpty(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef']->flipMap(^s: String => Integer :: s->length);");
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testFlipMapNonEmptyError(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef']->flipMap(^String => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testFlipMapReturnTypeNoError(): void {
		$result = $this->executeCodeSnippet("flipMap['a', 'bcd', 'ef']",
			valueDeclarations: "
				flipMap = ^p: Array<String<1..3>, 2..5> => Map<String<1..3>:Integer, 1..5> :: 
					p->flipMap(^s: String => Integer :: s->length);
			"
		);
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testFlipMapReturnTypeResultNoError(): void {
		$result = $this->executeCodeSnippet("flipMap['a', 'bcd', 'ef']",
			valueDeclarations: "
				flipMap = ^p: Array<String<1..3>, 2..5> => Result<Map<String<1..3>:Integer, 1..5>, String> :: 
					p->flipMap(^s: String => Result<Integer, String> :: s->length);
			"
		);
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testFlipMapReturnTypeResultError(): void {
		$result = $this->executeCodeSnippet("flipMap['a', 'bcd', 'ef']",
			valueDeclarations: "
				flipMap = ^p: Array<String<1..3>, 2..5> => Result<Map<String<1..3>:Integer, 1..5>, String> :: 
					p->flipMap(^s: String => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testFlipMapInvalidTargetType(): void {
		$this->executeErrorCodeSnippet("The item type of the target array must be a subtype of String, got (Integer[1]|String['a'])", "[1, 'a']->flipMap(^s: String => Integer :: s->length);");
	}

	public function testFlipMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['a', 'bcd', 'ef']->flipMap(5);");
	}

	public function testFlipMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Boolean of the callback function is not a supertype of String['a', 'bcd', 'ef']",
			"['a', 'bcd', 'ef']->flipMap(^Boolean => Boolean :: true);");
	}

}