<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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
		$this->executeErrorCodeSnippet('Target ref type must be an Array, Map or Set type, got: String',
			"type{String}->itemType;");
	}

}