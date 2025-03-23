<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint;

use Walnut\Lang\Implementation\Program\EntryPoint\Cli\CliEntryPointFactory;
use Walnut\Lang\Test\BaseProgramTestHelper;

class CliEntryPointFactoryTest extends BaseProgramTestHelper {

	public function testCall(): void {
		$factory = new CliEntryPointFactory('root', ['a' => 'b']);
		$ep = $factory->entryPoint;
		$this->assertNotNull($ep);
	}

}