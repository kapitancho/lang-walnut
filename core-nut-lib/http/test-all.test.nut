module $http/test-all %% $test/runner, $http/bundle/autowire-router,
    $http/autowire/request-helper, $http/autowire/response-helper, $http/autowire/view-response-helper:

/* to run this test file, use 'php -f cli/test.php ../core-nut-lib/http' */

DependencyContainerError ==> HttpResponse %% [~ HttpResponseBuilder] ::
    %httpResponseBuilder(500)->withBody('Dependency container error: ' + $->errorMessage + ': ' + $->targetType->asString);

UnableToRenderTemplate ==> HttpResponse %% [~ HttpResponseBuilder] ::
    %httpResponseBuilder(500)->withBody($->asString);

IndexHandler = :[];
IndexHandler ==> HttpRequestHandler %% [~HttpResponseBuilder] :: ^request: {HttpRequest} => {HttpResponse} :: {
    %httpResponseBuilder(200)->withBody('<h1>Hello world</h1>' + request->shape(`HttpRequest).target)
};

AboutHandler = :[];
AboutHandler ==> HttpRequestHandler %% [~HttpResponseBuilder] :: ^request: {HttpRequest} => {HttpResponse} :: {
    %httpResponseBuilder(200)->withBody('<h1>About</h1>' + request->shape(`HttpRequest).body->asString)
};

SpecialService = :[];
SpecialServiceError = $[message: String];
SpecialServiceError ==> HttpResponse %% [~HttpResponseBuilder] :: {
    %httpResponseBuilder(400)->withBody($message)
};
SpecialService->invoke(^param: String => Result<String, SpecialServiceError>) :: ?whenValueOf(param) is {
    '': @SpecialServiceError['Empty string'],
    ~: param->reverse
};

SpecialHandler = :[];
SpecialHandler ==> HttpRequestHandler %% [~HttpResponseBuilder, ~SpecialService] :: ^request: {HttpRequest} => {HttpResponse} :: {
    message = %specialService(request->shape(`HttpRequest).body->asString);
    ?whenIsError(message) {
        message->error
    } ~ {
        %httpResponseBuilder(200)->withBody('<h1>' + message + '</h1>')
    }
};

MyHandler = ^[p: String] => String;
==> MyHandler :: ^[p: String] => String :: #p->reverse;

==> HttpLookupRouterMapping :: [
    [path: '/about', type: type{AboutHandler}, method: HttpRequestMethod.post],
    [path: '/special', type: type{SpecialHandler}],
    [path: '/index', type: type{IndexHandler}],
    [path: '', type: type{HttpAutoWireRequestHandler}]
];

JJ = ^[j: JsonValue] => JsonValue;
==> JJ :: ^[j: JsonValue] => JsonValue :: #j;

MyPatcher = ^[j: JsonValue, p: String] => Null;
==> MyPatcher :: ^[j: JsonValue, p: String] => Null :: null;

MyLister = ^[:] => String;
==> MyLister :: ^[:] => String :: 'List Response';

MyRedirect = ^[:] => String;
==> MyRedirect :: ^[:] => String :: '/redirect-url';

MyBrokenView = #String;
MyBrokenViewRenderer = ^[:] => MyBrokenView;
==> MyBrokenViewRenderer :: ^[:] => MyBrokenView :: MyBrokenView('Broken View Response');

MyView = #String;
MyView ==> Template @ UnableToRenderTemplate :: ?whenValueOf($$) is {
    '': @UnableToRenderTemplate[$->type],
    ~: {
        v = mutable{String, ''};
        v->SET('rendered view content: ' + $$);
        Template(v)
    }
};

MyViewRenderer = ^[:] => MyView;
==> MyViewRenderer :: ^[:] => MyView :: MyView('View Response');

MyEmptyViewRenderer = ^[:] => MyView;
==> MyEmptyViewRenderer :: ^[:] => MyView :: MyView('');

==> HttpAutoWireRequestHandler :: HttpAutoWireRequestHandler[
    routes: [
        HttpAutoWireRoute[
            method: HttpRequestMethod.post,
            pattern: RoutePattern('/jj'),
            requestBody: JsonRequestBody['j'],
            handler: `JJ,
            response: JsonResponseBody[statusCode: 200]
        ],
        HttpAutoWireRoute[
            method: HttpRequestMethod.get,
            pattern: RoutePattern('/abc/{p}'),
            requestBody: ^{HttpRequest} => Result<Map<JsonValue>, Any> :: [:],
            handler: `MyHandler,
            response: ^result: Any => Result<{HttpResponse}, Any> %% [~HttpResponseBuilder] :: {
                %httpResponseBuilder(200)->withBody('Result = ' + result->printed)
            }
        ],
        HttpAutoWireRoute[
            method: HttpRequestMethod.patch,
            pattern: RoutePattern('/abc/{p}'),
            requestBody: JsonRequestBody['j'],
            handler: `MyPatcher,
            response: NoResponseBody[statusCode: 204]
        ],
        HttpAutoWireRoute[
            method: HttpRequestMethod.get,
            pattern: RoutePattern('/abc'),
            requestBody: EmptyRequestBody(),
            handler: `MyLister,
            response: ContentResponseBody[statusCode: 200, contentType: 'text/plain']
        ],
        HttpAutoWireRoute[
            method: HttpRequestMethod.get,
            pattern: RoutePattern('/redirect-me'),
            requestBody: EmptyRequestBody(),
            handler: `MyRedirect,
            response: RedirectResponseBody[statusCode: 201]
        ],
        HttpAutoWireRoute[
            method: HttpRequestMethod.get,
            pattern: RoutePattern('/view/broken'),
            requestBody: EmptyRequestBody(),
            handler: `MyBrokenViewRenderer,
            response: ViewResponseBody[statusCode: 200, contentType: 'text/html']
        ],
        HttpAutoWireRoute[
            method: HttpRequestMethod.get,
            pattern: RoutePattern('/view/abc/empty'),
            requestBody: EmptyRequestBody(),
            handler: `MyEmptyViewRenderer,
            response: ViewResponseBody[statusCode: 200, contentType: 'text/html']
        ],
        HttpAutoWireRoute[
            method: HttpRequestMethod.get,
            pattern: RoutePattern('/view/abc'),
            requestBody: EmptyRequestBody(),
            handler: `MyViewRenderer,
            response: ViewResponseBody[statusCode: 200, contentType: 'text/html']
        ]
    ]
];

==> TestCases %% [h: HttpCompositeRequestHandler] :: {
    callExtended = ^[method: HttpRequestMethod, target: String, body: String] =>
            [statusCode: HttpResponseStatusCode, body: String|Null, headers: HttpHeaders] :: {
        resp = {%h->shape(`HttpRequestHandler)[
            protocolVersion: HttpProtocolVersion.http_1_1,
            method: #method,
            target: #target,
            headers: [:],
            body: #body
        ]}->shape(`HttpResponse);
        [statusCode: resp.statusCode, body: resp.body, headers: resp.headers]
    };
    call = ^[method: HttpRequestMethod, target: String, body: String] =>
            [statusCode: HttpResponseStatusCode, body: String|Null] :: {
        resp = callExtended(#);
        [statusCode: resp.statusCode, body: resp.body]
    };
    [
        ^ => TestResult :: TestResult[
            name: 'Not found response',
            expected: [statusCode: 404, body: 'Route not found: /non-existing-url'],
            actual = ^ :: call[HttpRequestMethod.get, '/non-existing-url', '']
        ],
        ^ => TestResult :: TestResult[
            name: 'Index test',
            expected: [statusCode: 200, body: '<h1>Hello world</h1>/abc'],
            actual = ^ :: call[HttpRequestMethod.get, '/index/abc', '']
        ],
        ^ => TestResult :: TestResult[
            name: 'About test',
            expected: [statusCode: 200, body: '<h1>About</h1>body'],
            actual = ^ :: call[HttpRequestMethod.post, '/about', 'body']
        ],
        ^ => TestResult :: TestResult[
            name: 'Special test',
            expected: [statusCode: 200, body: '<h1>gnirts ytpmE</h1>'],
            actual = ^ :: call[HttpRequestMethod.get, '/special', 'Empty string']
        ],
        ^ => TestResult :: TestResult[
            name: 'Special test empty body',
            expected: [statusCode: 400, body: 'Empty string'],
            actual = ^ :: call[HttpRequestMethod.get, '/special', '']
        ],
        ^ => TestResult :: TestResult[
            name: 'About test get (skips AboutHandler)',
            expected: [statusCode: 200, body: 'Result = \`fed\`'],
            actual = ^ :: call[HttpRequestMethod.get, '/abc/def', 'body']
        ],
        ^ => TestResult :: TestResult[
            name: 'Autowire Json2Json test',
            expected: [statusCode: 200, body: '[\n    1,\n    2,\n    3\n]'],
            actual = ^ :: call[HttpRequestMethod.post, '/jj', '[1, 2, 3]']
        ],
        ^ => TestResult :: TestResult[
            name: 'Autowire Patch no resposne',
            expected: [statusCode: 204, body: null],
            actual = ^ :: call[HttpRequestMethod.patch, '/abc/def', '[1, 2, 3]']
        ],
        ^ => TestResult :: TestResult[
            name: 'Autowire List response',
            expected: [statusCode: 200, body: 'List Response'],
            actual = ^ :: call[HttpRequestMethod.get, '/abc', '']
        ],
        ^ => TestResult :: TestResult[
            name: 'Autowire Redirect response',
            expected: [
                statusCode: 201,
                body: null,
                headers: [
                    Location: ['/redirect-url'],
                    'Access-Control-Allow-Origin': ['*'],
                    'Access-Control-Allow-Headers': [
                        'Content-Type, Authorization, Location'
                    ],
                    'Access-Control-Expose-Headers': [
                        'Content-Type, Authorization, Location'
                    ],
                    'Access-Control-Allow-Methods': [
                       'options, get, head, post, put, patch, delete'
                    ]
                ]
            ],
            actual = ^ :: callExtended[HttpRequestMethod.get, '/redirect-me', '']
        ],
        ^ => TestResult :: TestResult[
            name: 'Autowire Broken View response',
            expected: [
                statusCode: 500,
                body: 'Unable to render template of type MyBrokenView'
            ],
            actual = ^ :: call[HttpRequestMethod.get, '/view/broken', '']
        ],
        ^ => TestResult :: TestResult[
            name: 'Autowire View response not accepted',
            expected: [statusCode: 500, body: 'Unable to render template of type MyView'],
            actual = ^ :: call[HttpRequestMethod.get, '/view/abc/empty', '']
        ],
        ^ => TestResult :: TestResult[
            name: 'Autowire View response',
            expected: [statusCode: 200, body: 'rendered view content: View Response'],
            actual = ^ :: call[HttpRequestMethod.get, '/view/abc', '']
        ]
    ]
};