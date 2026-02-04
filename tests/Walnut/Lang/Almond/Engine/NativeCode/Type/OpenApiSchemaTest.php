<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class OpenApiSchemaTest extends CodeExecutionTestHelper {

	public function testOpenApiSchemaJsonValue(): void {
		$result = $this->executeCodeSnippet("type{JsonValue}->openApiSchema;");
		$this->assertEquals("[type: 'any']", $result);
	}

	public function testOpenApiSchemaInteger(): void {
		$result = $this->executeCodeSnippet("type{Integer<(2..5]>}->openApiSchema;");
		$this->assertEquals("[\n\ttype: 'integer',\n\tminimum: 2,\n\texclusiveMinimum: true,\n\tmaximum: 5,\n\texclusiveMaximum: false\n]", $result);
	}

	public function testOpenApiSchemaMutable(): void {
		$result = $this->executeCodeSnippet("type{Mutable<Integer<(2..5]>>}->openApiSchema;");
		$this->assertEquals("[\n\ttype: 'integer',\n\tminimum: 2,\n\texclusiveMinimum: true,\n\tmaximum: 5,\n\texclusiveMaximum: false\n]", $result);
	}

	public function testOpenApiSchemaIntegerNoRange(): void {
		$result = $this->executeCodeSnippet("type{Integer}->openApiSchema;");
		$this->assertEquals("[type: 'integer']", $result);
	}

	public function testOpenApiSchemaIntegerMultipleIntervals(): void {
		$result = $this->executeCodeSnippet("type{Integer<[1..5], [10..20]>}->openApiSchema;");
		$this->assertStringContainsString("oneOf", $result);
	}

	public function testOpenApiSchemaReal(): void {
		$result = $this->executeCodeSnippet("type{Real<1.5..3.5>}->openApiSchema;");
		$this->assertEquals("[\n\ttype: 'number',\n\tminimum: 1.5,\n\texclusiveMinimum: false,\n\tmaximum: 3.5,\n\texclusiveMaximum: false\n]", $result);
	}

	public function testOpenApiSchemaRealNoRange(): void {
		$result = $this->executeCodeSnippet("type{Real}->openApiSchema;");
		$this->assertEquals("[type: 'number']", $result);
	}

	public function testOpenApiSchemaBoolean(): void {
		$result = $this->executeCodeSnippet("type{Boolean}->openApiSchema;");
		$this->assertEquals("[type: 'boolean']", $result);
	}

	public function testOpenApiSchemaNull(): void {
		$result = $this->executeCodeSnippet("type{Null}->openApiSchema;");
		$this->assertEquals("[type: 'null']", $result);
	}

	public function testOpenApiSchemaString(): void {
		$result = $this->executeCodeSnippet("type{String}->openApiSchema;");
		$this->assertEquals("[type: 'string']", $result);
	}

	public function testOpenApiSchemaStringWithLength(): void {
		$result = $this->executeCodeSnippet("type{String<1..50>}->openApiSchema;");
		$this->assertEquals("[\n\ttype: 'string',\n\tminLength: 1,\n\tmaxLength: 50\n]", $result);
	}

	public function testOpenApiSchemaStringSubset(): void {
		$result = $this->executeCodeSnippet("type{String['red', 'green', 'blue']}->openApiSchema;");
		$this->assertStringContainsString("'string'", $result);
		$this->assertStringContainsString("enum", $result);
		$this->assertStringContainsString("'red'", $result);
	}

	public function testOpenApiSchemaArray(): void {
		$result = $this->executeCodeSnippet("type{Array<String>}->openApiSchema;");
		$this->assertStringContainsString("'array'", $result);
		$this->assertStringContainsString("items", $result);
		$this->assertStringContainsString("'string'", $result);
	}

	public function testOpenApiSchemaArrayWithLength(): void {
		$result = $this->executeCodeSnippet("type{Array<Integer<1..10>, 1..5>}->openApiSchema;");
		$this->assertStringContainsString("'array'", $result);
		$this->assertStringContainsString("minItems: 1", $result);
		$this->assertStringContainsString("maxItems: 5", $result);
	}

	public function testOpenApiSchemaSet(): void {
		$result = $this->executeCodeSnippet("type{Set<Integer>}->openApiSchema;");
		$this->assertStringContainsString("'array'", $result);
		$this->assertStringContainsString("uniqueItems: true", $result);
	}

	public function testOpenApiSchemaSetWithLength(): void {
		$result = $this->executeCodeSnippet("type{Set<String, 2..10>}->openApiSchema;");
		$this->assertStringContainsString("uniqueItems: true", $result);
		$this->assertStringContainsString("minItems: 2", $result);
		$this->assertStringContainsString("maxItems: 10", $result);
	}

	public function testOpenApiSchemaMap(): void {
		$result = $this->executeCodeSnippet("type{Map<Integer>}->openApiSchema;");
		$this->assertStringContainsString("'object'", $result);
		$this->assertStringContainsString("additionalProperties", $result);
	}

	public function testOpenApiSchemaMapWithLength(): void {
		$result = $this->executeCodeSnippet("type{Map<String, 1..20>}->openApiSchema;");
		$this->assertStringContainsString("'object'", $result);
		$this->assertStringContainsString("minProperties: 1", $result);
		$this->assertStringContainsString("maxProperties: 20", $result);
	}

	public function testOpenApiSchemaRecord(): void {
		$result = $this->executeCodeSnippet("type{[name: String, age: Integer]}->openApiSchema;");
		$this->assertStringContainsString("'object'", $result);
		$this->assertStringContainsString("properties", $result);
		$this->assertStringContainsString("name", $result);
		$this->assertStringContainsString("age", $result);
	}

	public function testOpenApiSchemaRecordWithOptional(): void {
		$result = $this->executeCodeSnippet("type{[name: String, age: OptionalKey<Integer>]}->openApiSchema;");
		$this->assertStringContainsString("name", $result);
		$this->assertStringContainsString("age", $result);
		$this->assertStringContainsString("required", $result);
	}

	public function testOpenApiSchemaUnion(): void {
		$result = $this->executeCodeSnippet("type{String | Integer}->openApiSchema;");
		$this->assertStringContainsString("oneOf", $result);
	}

	public function testOpenApiSchemaIntersection(): void {
		$result = $this->executeCodeSnippet("type{[a: Integer] & [b: String]}->openApiSchema;");
		$this->assertStringContainsString("allOf", $result);
	}

	public function testOpenApiSchemaNamedType(): void {
		$result = $this->executeCodeSnippet("type{User}->openApiSchema;", "User = [id: Integer, name: String];");
		$this->assertStringContainsString("'#/components/schemas/User'", $result);
	}

}