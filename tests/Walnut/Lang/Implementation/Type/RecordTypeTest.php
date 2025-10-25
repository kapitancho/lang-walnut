<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class RecordTypeTest extends BaseProgramTestHelper {

	public function testRecordTypeTypeOf(): void {
		$type = $this->typeRegistry->record([
			'a' => $this->typeRegistry->boolean,
			'b' => $this->typeRegistry->integer()
		]);
		$this->assertEquals($this->typeRegistry->boolean, $type->typeOf('a'));
	}

}