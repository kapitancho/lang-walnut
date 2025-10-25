<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AddTest extends CodeExecutionTestHelper {

	public function testAddNew(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->ADD(5);");
		$this->assertEquals("mutable{Set, [1; 2; 3; 5]}", $result);
	}

	public function testAddExisting(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->ADD(2);");
		$this->assertEquals("mutable{Set, [1; 2; 3]}", $result);
	}

	public function testAddInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->ADD(2);");
	}

	public function testAddInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Set<Integer>, [1; 2; 3]}->ADD('hi');");
	}

}