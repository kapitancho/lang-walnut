<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BracketsTest extends CodeExecutionTestHelper {

	public function testTypesInBrackets(): void {
		$result = $this->executeCodeSnippet("type[Type1, Type2, Type3, Type4];", <<<NUT
		Type1 = (Integer|String)&Boolean;
		Type2 = (Integer&String)|Boolean;
		Type3 = Integer|(String&Boolean);
		Type4 = Integer&(String|Boolean);
	NUT);
		$this->assertEquals("type[Type1, Type2, Type3, Type4]", $result);
	}


}