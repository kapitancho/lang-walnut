<?php

namespace Walnut\Lang\Test\Implementation\Program\EntryPoint;

use Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\HttpEntryPointFactory;
use Walnut\Lang\Test\BaseProgramTestHelper;

class HttpEntryPointFactoryTest extends BaseProgramTestHelper {

	public function testCall(): void {
		$factory = new HttpEntryPointFactory(new PackageConfiguration('root', ['a' => 'b']));
		$ep = $factory->entryPoint;
		$this->assertNotNull($ep);
	}

}