<?php

namespace Walnut\Lang\Test;

final class CodeExecutionTest extends CodeExecutionTestHelper {

	public function testCodeExecutionOk(): void {
		$result = $this->executeCodeSnippet('{#=>item(0)=>asInteger} + {#=>item(1)=>asInteger};', parameters: [2, 3]);
		$this->assertEquals('5', $result);
	}

	public function testCodeExecutionContextOk(): void {
		$result = $this->executeCodeSnippet('{#=>item(0)=>asInteger} + getConst();', 'getConst = ^Any => Integer :: 7;', [5]);
		$this->assertEquals('12', $result);
	}

}