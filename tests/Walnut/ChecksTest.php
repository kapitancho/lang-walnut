<?php

namespace Walnut;

use InvalidArgumentException;
use Walnut\Lang\Test\BaseProgramTestHelper;

class ChecksTest extends BaseProgramTestHelper {

	public function testTupleConstruction(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->valueRegistry->tuple(['something']);
	}

	public function testRecordConstruction(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->valueRegistry->record(['something']);
	}
}