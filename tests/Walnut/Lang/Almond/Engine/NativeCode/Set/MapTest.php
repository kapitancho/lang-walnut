<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testSetEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[;]", $result);
	}

	public function testSetNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[4; 5; 8; 13]", $result);
	}

	public function testSetNonUnique(): void {
		$result = $this->executeCodeSnippet("['hello'; 'world'; 'hi']->map(^s: String => Integer :: s->length);");
		$this->assertEquals("[5; 2]", $result);
	}

	public function testSetNonEmptyEmptyValue(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->map(^Integer => Optional<Integer> :: {
			x = # + 3;
			?whenTypeOf(x) { `Integer<10..>: empty, ~: x }
		});");
		$this->assertEquals("[4; 5; 8]", $result);
	}

	public function testSetNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->map(^Integer => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testSetReturnTypeNoError(): void {
		$result = $this->executeCodeSnippet("fn[1; 2; 5]",
			valueDeclarations: "
				fn = ^p: Set<Integer, 2..5> => Set<Integer, 1..5> ::
					p->map(^v: Integer => Integer :: v + 1);
			"
		);
		$this->assertEquals("[2; 3; 6]", $result);
	}

	public function testSetReturnTypeNoErrorOptional(): void {
		$result = $this->executeCodeSnippet("fn[1; 2; 5]",
			valueDeclarations: "
				fn = ^p: Set<Integer, 2..5> => Set<Integer, ..5> ::
					p->map(^v: Integer => Optional<Integer> :: {
						?whenTypeOf(v) { `Integer<5..>: empty, ~: v + 1 }
					});
			"
		);
		$this->assertEquals("[2; 3]", $result);
	}

	public function testSetReturnTypeResultNoError(): void {
		$result = $this->executeCodeSnippet("fn[1; 2; 5]",
			valueDeclarations: "
				fn = ^p: Set<Integer, 2..5> => Result<Set<Integer, 1..5>, String> ::
					p->map(^v: Integer => Result<Integer, String> :: v + 1);
			"
		);
		$this->assertEquals("[2; 3; 6]", $result);
	}

	public function testSetReturnTypeResultNoErrorEmpty(): void {
		$result = $this->executeCodeSnippet("fn[1; 2; 5]",
			valueDeclarations: "
				fn = ^p: Set<Integer, 2..5> => Result<Set<Integer, ..5>, String> ::
					p->map(^v: Integer => Result<Optional<Integer>, String> :: {
						?whenTypeOf(v) { `Integer<5..>: empty, ~: v + 1 }
					});
			"
		);
		$this->assertEquals("[2; 3]", $result);
	}

	public function testSetReturnTypeResultError(): void {
		$result = $this->executeCodeSnippet("fn[1; 2; 5]",
			valueDeclarations: "
				fn = ^p: Set<Integer, 2..5> => Result<Set<Integer, 1..5>, String> ::
					p->map(^v: Integer => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a']->map(5);");
	}

	public function testSetInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The item type (Integer[1]|String['a']) is not a subtype of the of the callback function parameter type Boolean",
			"[1; 'a']->map(^Boolean => Boolean :: true);");
	}

}