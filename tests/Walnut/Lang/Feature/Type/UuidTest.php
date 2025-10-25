<?php

namespace Walnut\Lang\Test\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UuidTest extends CodeExecutionTestHelper {

	public function testUuidFromStringFixValid(): void {
		$result = $this->executeCodeSnippet("getUuid();", valueDeclarations: <<<NUT
		getUuid = ^ => Uuid :: Uuid('00000000-0000-4000-9000-000000000000');
	NUT);
		$this->assertEquals("Uuid{'00000000-0000-4000-9000-000000000000'}", $result);
	}

	public function testUuidFromStringFixInvalid(): void {
		$result = $this->executeCodeSnippet("getUuid();", valueDeclarations: <<<NUT
		getUuid = ^ => Result<Uuid, InvalidUuid> :: Uuid('00000000-0000-4000-0000-000000000000');
	NUT);
		$this->assertEquals("@InvalidUuid!'00000000-0000-4000-0000-000000000000'", $result);
	}

	public function testUuidFromStringFixInvalidError(): void {
		$this->executeErrorCodeSnippet(
			"expected a return value of type Uuid, got Result<Uuid, InvalidUuid>",
			"getUuid();", valueDeclarations:  <<<NUT
				getUuid = ^ => Uuid :: Uuid('00000000-0000-0000-9000-000000000000');
			NUT);
	}

	public function testUuidFromStringParamValid(): void {
		$result = $this->executeCodeSnippet("getUuid('00000000-0000-4000-9000-000000000000');", valueDeclarations: <<<NUT
		getUuid = ^uuid: String<36> => Result<Uuid, InvalidUuid> :: Uuid(uuid);
	NUT);
		$this->assertEquals("Uuid{'00000000-0000-4000-9000-000000000000'}", $result);
	}

	public function testUuidFromStringParamInvalid(): void {
		$result = $this->executeCodeSnippet("getUuid('00000000-0000-4000-0000-000000000000');", valueDeclarations: <<<NUT
		getUuid = ^uuid: String<36> => Result<Uuid, InvalidUuid> :: Uuid(uuid);
	NUT);
		$this->assertEquals("@InvalidUuid!'00000000-0000-4000-0000-000000000000'", $result);
	}

}