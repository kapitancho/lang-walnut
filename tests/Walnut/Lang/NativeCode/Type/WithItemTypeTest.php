<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithItemTypeTest extends CodeExecutionTestHelper {

	public function testWithItemTypeArray(): void {
		$result = $this->executeCodeSnippet("type{Array<String>}->withItemType(`Integer);");
		$this->assertEquals("type{Array<Integer>}", $result);
	}

	public function testWithItemTypeMap(): void {
		$result = $this->executeCodeSnippet("type{Map<String<2>:String, 1..3>}->withItemType(`Integer);");
		$this->assertEquals("type{Map<String<2>:Integer, 1..3>}", $result);
	}

	public function testWithItemTypeSet(): void {
		$result = $this->executeCodeSnippet("type{Set<String>}->withItemType(`Integer);");
		$this->assertEquals("type{Set<Integer>}", $result);
	}

	public function testWithItemTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withItemType(`Integer);");
	}

	public function testWithItemTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Set<String>}->withItemType(1)");
	}

}