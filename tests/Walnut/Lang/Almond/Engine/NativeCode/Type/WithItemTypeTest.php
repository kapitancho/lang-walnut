<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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
		$this->executeErrorCodeSnippet('Target ref type must be an Array type, a Map type or a Set type, got: String',
			"type{String}->withItemType(`Integer);");
	}

	public function testWithItemTypeArrayWithRange(): void {
		$result = $this->executeCodeSnippet("`Array<String, 2..3>->withItemType(`Integer);");
		$this->assertEquals("type{Array<Integer, 2..3>}", $result);
	}

	public function testWithItemTypeMapWithRange(): void {
		$result = $this->executeCodeSnippet("`Map<String, 2..3>->withItemType(`Integer);");
		$this->assertEquals("type{Map<Integer, 2..3>}", $result);
	}

	public function testWithItemTypeSetWithRange(): void {
		$result = $this->executeCodeSnippet("`Set<String, 2..3>->withItemType(`Integer);");
		$this->assertEquals("type{Set<Integer, 2..3>}", $result);
	}

	public function testWithItemTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type: Integer[1]',
			"type{Set<String>}->withItemType(1)");
	}

}