<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class OpenApiSchemaTest extends CodeExecutionTestHelper {

	public function testOpenApiSchemaInteger(): void {
		$result = $this->executeCodeSnippet("type{Integer<(2..5]>}->openApiSchema;");
		$this->assertEquals("[\n\ttype: 'integer',\n\tminimum: 2,\n\texclusiveMinimum: true,\n\tmaximum: 5,\n\texclusiveMaximum: false\n]", $result);
	}

	//TODO - add all other cases

}