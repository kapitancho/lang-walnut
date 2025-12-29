<?php

namespace Walnut\Lang\Test\NativeCode\Bytes;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ChunkTest extends CodeExecutionTestHelper {

	public function testChunkOk(): void {
		$result = $this->executeCodeSnippet('"hello"->chunk(2);');
		$this->assertEquals('["he", "ll", "o"]', $result);
	}

	public function testChunkNotNeeded(): void {
		$result = $this->executeCodeSnippet('"hello"->chunk(10);');
		$this->assertEquals('["hello"]', $result);
	}

	public function testChunkInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->chunk(false);');
	}

}
