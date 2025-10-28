<?php

namespace Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JsonFilePackageConfigurationProviderTest extends TestCase {

	public function testJsonOk(): void {
		$provider = new JsonFilePackageConfigurationProvider(
			'data:application/json,{"sourceRoot":"\/var\/www\/app","packages":{"core":"\/var\/www\/app\/core","utils":"\/var\/www\/app\/utils"}}'
		);
		$cfg = $provider->packageConfiguration;
		$this->assertEquals('/var/www/app', $cfg->defaultRoot);
		$this->assertEquals(
			[
				'core' => '/var/www/app/core',
				'utils' => '/var/www/app/utils'
			],
			$cfg->packageRoots
		);
	}

	#[DataProvider('invalidJsonProvider')]
	public function testJsonNotAnArray(string $value): void {
		$this->expectException(RuntimeException::class);
		new JsonFilePackageConfigurationProvider($value)->packageConfiguration;
	}

	public static function invalidJsonProvider(): iterable {
		return [
			['some non existing file'],
			['data:application/json,invalid json'],
			['data:application/json,null'],
			['data:application/json,{"packages":{"core":"\/var\/www\/app\/core","utils":"\/var\/www\/app\/utils"}}'],
			['data:application/json,{"sourceRoot":null,"packages":{"core":"\/var\/www\/app\/core","utils":"\/var\/www\/app\/utils"}}'],
			['data:application/json,{"sourceRoot":"\/var\/www\/app"}'],
			['data:application/json,{"sourceRoot":"\/var\/www\/app","packages":null}'],
			['data:application/json,{"sourceRoot":"\/var\/www\/app","packages":{"core":"\/var\/www\/app\/core","utils":null}}']
		];
	}
}