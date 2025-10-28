<?php

namespace Walnut\Lang\Test\Implementation\Program\EntryPoint;

use Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Implementation\Program\EntryPoint\Cli\CliEntryPointFactory;
use Walnut\Lang\Test\BaseProgramTestHelper;

class CliEntryPointFactoryTest extends BaseProgramTestHelper {

	public function testCall(): void {
		$factory = new CliEntryPointFactory(
			new PackageConfiguration('root', ['a' => 'b'])
		);
		$ep = $factory->entryPoint;
		$this->assertNotNull($ep);
	}

}