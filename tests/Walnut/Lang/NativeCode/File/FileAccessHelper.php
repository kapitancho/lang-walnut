<?php

namespace Walnut\Lang\Test\NativeCode\File;

use Walnut\Lang\Test\CodeExecutionTestHelper;

abstract class FileAccessHelper extends CodeExecutionTestHelper {

	protected string $tempDirPath;
	protected string $tempFilePath;

	public function setUp(): void {
		parent::setUp();

		$dir = sys_get_temp_dir(); // or any directory you want
		$this->tempDirPath = $dir . DIRECTORY_SEPARATOR . uniqid('prefix_', true);
		mkdir($this->tempDirPath);
		$this->tempFilePath = $this->tempDirPath . DIRECTORY_SEPARATOR . uniqid('prefix_', true);
	}

	protected function tearDown(): void {
		if (file_exists($this->tempFilePath)) {
			chmod($this->tempFilePath, 0777);
			unlink($this->tempFilePath);
		}
		if (is_dir($this->tempDirPath)) {
			chmod($this->tempDirPath, 0777);
			rmdir($this->tempDirPath);
		}
		parent::tearDown();
	}

}
