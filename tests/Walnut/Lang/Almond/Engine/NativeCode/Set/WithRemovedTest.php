<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class WithRemovedTest extends CodeExecutionTestHelper {

	public function testWithRemovedEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->withRemoved(3);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithRemovedNotFound(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->withRemoved(3);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithRemovedNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->withRemoved(5);");
		$this->assertEquals("[1; 2; 10]", $result);
	}

	public function testWithRemovedReturnType(): void {
		$result = $this->executeCodeSnippet(
			"fn[1; 2; 5; 10; 5];",
			valueDeclarations: "fn = ^s: Set<Integer, ..5> => Result<Set<Integer, ..5>, ItemNotFound> :: s->withRemoved(5);",
		);
		$this->assertEquals("[1; 2; 10]", $result);
	}

}