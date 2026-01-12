<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMapStringKey(): void {
		$result = $this->executeCodeSnippet("`Map<String<1..3>|String['hello']:Real>;");
		$this->assertEquals("type{Map<(String<1..3>|String['hello']):Real>}", $result);
	}

	public function testMapStringKeyAlias(): void {
		$result = $this->executeCodeSnippet(
			"`Map<MyString:Real>;",
			"MyString = String<1..3>|String['hello'];",
		);
		$this->assertEquals("type{Map<MyString:Real>}", $result);
	}

	public function testStrictSupersetCastError(): void {
		// The cast may return an error and therefore an implicit usage is not allowed.
		$this->executeErrorCodeSnippet(
			"The map key type 'Integer' must be a subset of the String type",
			"`Map<Integer:String>"
		);
	}

}