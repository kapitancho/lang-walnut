<?php

namespace Walnut\Lang\Feature\Http;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequestMethod;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\Message\HttpResponse;
use Walnut\Lang\Test\CodeExecutionTestHelper;

final class HttpSmokeTest extends CodeExecutionTestHelper {

	public function testEverything(): void {
		$result = $this->executeCodeSnippetAsHttp(
			"{%httpResponseBuilder(200)->withHeader[
				headerName: 'Content-Type',
				values: ['application/json']
			]}->withBody(request.body->asString->reverse);",
			<<<NUT
				Type1 = (Integer|String)&Boolean;
			NUT,
			new HttpRequest(
				HttpProtocolVersion::http_1_1,
				HttpRequestMethod::post,
				'/',
				[
					'Content-Type' => ['application/json']
				],
				'{"hello": "world"}'
			)
		);
		$this->assertEquals(
			new HttpResponse(
				HttpProtocolVersion::http_1_1,
				200,
				['Content-Type' => ['application/json']],
				'}"dlrow" :"olleh"{'
			),
			$result);
	}
}