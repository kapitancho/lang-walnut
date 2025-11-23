<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class TupleTypeTest extends BaseProgramTestHelper {

	public function testRecordTypeTypeOf(): void {
		$type = $this->typeRegistry->tuple([
			$this->typeRegistry->boolean,
			$this->typeRegistry->integer()
		]);
		$this->assertEquals($this->typeRegistry->boolean, $type->typeOf(0));
	}

}