<?php

namespace Walnut\Lang\Test\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FlipMapTest extends CodeExecutionTestHelper {

	public function testFlipMapEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->flipMap(^s: String => Integer :: s->length);");
		$this->assertEquals("[:]", $result);
	}

	public function testFlipMapNonEmpty(): void {
		$result = $this->executeCodeSnippet("['a'; 'bcd'; 'ef']->flipMap(^s: String => Integer :: s->length);");
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testFlipMapNonEmptyError(): void {
		$result = $this->executeCodeSnippet("['a'; 'bcd'; 'ef']->flipMap(^String => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testFlipMapReturnTypeNoError(): void {
		$result = $this->executeCodeSnippet("flipMap['a'; 'bcd'; 'ef']",
			valueDeclarations: "
				flipMap = ^p: Set<String<1..3>, 2..5> => Map<String<1..3>:Integer, 2..5> :: 
					p->flipMap(^s: String => Integer :: s->length);
			"
		);
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testFlipMapReturnTypeResultNoError(): void {
		$result = $this->executeCodeSnippet("flipMap['a'; 'bcd'; 'ef']",
			valueDeclarations: "
				flipMap = ^p: Set<String<1..3>, 2..5> => Result<Map<String<1..3>:Integer, 2..5>, String> :: 
					p->flipMap(^s: String => Result<Integer, String> :: s->length);
			"
		);
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testFlipMapReturnTypeResultError(): void {
		$result = $this->executeCodeSnippet("flipMap['a'; 'bcd'; 'ef']",
			valueDeclarations: "
				flipMap = ^p: Set<String<1..3>, 2..5> => Result<Map<String<1..3>:Integer, 2..5>, String> :: 
					p->flipMap(^s: String => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testFlipMapInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1; 'a']->flipMap(^s: String => Integer :: s->length);");
	}

	public function testFlipMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['a'; 'bcd'; 'ef']->flipMap(5);");
	}

	public function testFlipMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type String['a', 'bcd', 'ef'] of the callback function is not a subtype of Boolean",
			"['a'; 'bcd'; 'ef']->flipMap(^Boolean => Boolean :: true);");
	}

}