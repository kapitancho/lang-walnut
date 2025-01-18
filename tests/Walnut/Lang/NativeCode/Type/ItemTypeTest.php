<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemTypeTest extends CodeExecutionTestHelper {

	public function testItemTypeArray(): void {
		$result = $this->executeCodeSnippet("type{Array<String>}->itemType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testItemTypeMap(): void {
		$result = $this->executeCodeSnippet("type{Map<String>}->itemType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testItemTypeSet(): void {
		$result = $this->executeCodeSnippet("type{Set<String>}->itemType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testItemTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->itemType;");
	}

}