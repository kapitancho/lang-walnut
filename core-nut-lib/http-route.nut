module http-route %% http-core:

HttpRequestBodyToParameter = ^HttpRequest => Result<Map<JsonValue>, Any>;

EmptyRequestBody = :[];
EmptyRequestBody ==> HttpRequestBodyToParameter ::
    ^HttpRequest => Map<Nothing, 0..0> :: [:];

JsonRequestBody = $[valueKey: String];
JsonRequestBody ==> HttpRequestBodyToParameter ::
    ^request: HttpRequest => Result<Map<JsonValue>, InvalidJsonString> :: {
        body = request.body;
        body = ?whenTypeOf(body) is {
            type{String}: body,
            ~: ''
        };
        value = body=>jsonDecode;
        ?whenTypeOf(value) is {
            type{Result<Nothing, InvalidJsonString>}: value,
            type{JsonValue}: [:]->withKeyValue[key: $valueKey, value: value]
        }
    };

HttpResponseBodyFromParameter = ^Any => Result<HttpResponse, Any>;

NoResponseBody = $[statusCode: HttpStatusCode];
NoResponseBody ==> HttpResponseBodyFromParameter ::
    ^Any => HttpResponse :: [
         statusCode: $statusCode,
         protocolVersion: HttpProtocolVersion.HTTP11,
         headers: [:],
         body: null
];

RedirectResponseBody = $[statusCode: HttpStatusCode];
RedirectResponseBody ==> HttpResponseBodyFromParameter ::
    ^result: Any => Result<HttpResponse, Any> :: {
        redirectValue = result=>as(type{String});
        [
             statusCode: $statusCode,
             protocolVersion: HttpProtocolVersion.HTTP11,
             headers: [:]->withKeyValue[key: 'Location', value: [redirectValue]],
             body: null
        ]
    };

JsonResponseBody = $[statusCode: HttpStatusCode];
JsonResponseBody ==> HttpResponseBodyFromParameter ::
    ^result: Any => Result<HttpResponse, Any> :: {
        jsonValue = result->asJsonValue;
        ?whenIsError(jsonValue) { jsonValue } ~ {
            [
                 statusCode: $statusCode,
                 protocolVersion: HttpProtocolVersion.HTTP11,
                 headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
                 body: jsonValue->stringify
            ]
        }
    };

ContentResponseBody = $[statusCode: HttpStatusCode, contentType: String];
ContentResponseBody ==> HttpResponseBodyFromParameter ::
    ^result: Any => Result<HttpResponse, Any> :: {
        [
             statusCode: $statusCode,
             protocolVersion: HttpProtocolVersion.HTTP11,
             headers: [:]->withKeyValue[key: 'Content-Type', value: [$contentType]],
             body: result => asString
        ]
    };

RoutePattern <: String;
RoutePatternDoesNotMatch = :[];

HttpRouteDoesNotMatch = :[];
HttpRoute = $[
    method: HttpRequestMethod,
    pattern: RoutePattern,
    requestBody: HttpRequestBodyToParameter,
    handler: Type<^Nothing => Any>,
    response: HttpResponseBodyFromParameter
];

HttpRoute->handleRequest(^request: HttpRequest => Result<HttpResponse, HttpRouteDoesNotMatch>) %% DependencyContainer :: {
    err = ^result: Any => Result<HttpResponse, HttpRouteDoesNotMatch> :: {
        httpResponse = result->as(type{HttpResponse});
        ?whenTypeOf(httpResponse) is {
            type{HttpResponse}: httpResponse,
            ~: [
                statusCode: 500,
                protocolVersion: HttpProtocolVersion.HTTP11,
                headers: [:],
                body: ''->concatList['Cannot handle error type: ', #->type->asString]
            ]
        }
    };
    runner = ^Null => Result<HttpResponse, Any> :: {
        ?whenValueOf(request.method) is {
            $method: {
                matchResult = $pattern->matchAgainst(request.requestTarget);
                ?whenTypeOf(matchResult) is {
                    type{Map<String|Integer<0..>>}: {
                        bodyArg = $requestBody=>invoke(request);
                        callParams = matchResult->mergeWith(bodyArg);
                        callParams = callParams->asJsonValue;
                        handlerType = $handler;
                        handlerParameterType = handlerType->parameterType;
                        handlerReturnType = handlerType->returnType;
                        handlerParams = callParams=>hydrateAs(handlerParameterType);
                        handlerInstance = %=>valueOf(handlerType);
                        handlerResult = ?noError(handlerInstance(handlerParams));
                        $response=>invoke(handlerResult)
                    },
                    ~: Error(HttpRouteDoesNotMatch[])
                }
            },
            ~: Error(HttpRouteDoesNotMatch[])
        }
    };
    runnerResult = runner(null);
    ?whenTypeOf(runnerResult) is {
        type{Result<Nothing, HttpRouteDoesNotMatch>}: runnerResult,
        type{Result<Nothing, Any>}: err(runnerResult->error),
        type{HttpResponse}: runnerResult
    }
};

HttpRouteChain = $[routes: Array<HttpRoute, 1..>];
HttpRouteChain->handleRequest(^request: HttpRequest => Result<HttpResponse, HttpRouteDoesNotMatch>) :: {
    routes = $routes;

    h = ^routes: Array<HttpRoute, 1..> => Result<HttpResponse, HttpRouteDoesNotMatch> :: {
        split = routes->withoutFirst;
        route = split.element;
        rest = split.array;

        result = route->handleRequest(request);
        ?whenTypeOf(result) is {
            type{HttpResponse}: result,
            ~: {
                ?whenTypeOf(rest) is {
                    type{Array<HttpRoute, 1..>}: h(rest),
                    ~: result
                }
            }
        }
    };
    h(routes)
};

HttpRouteBuilder = :[];
HttpRouteBuilder->httpPostJsonLocation(^[pattern: RoutePattern, handler: Type<^Nothing => Any>, bodyArgName: String<1..>|Null] => HttpRoute) :: {
    a = #bodyArgName;
    requestBody = ?whenTypeOf(a) is {
        type{String}: JsonRequestBody[a],
        ~: EmptyRequestBody[]
    };
    HttpRoute[
        method: HttpRequestMethod.POST,
        pattern: #pattern,
        requestBody: requestBody->asHttpRequestBodyToParameter,
        handler: #handler,
        response: {RedirectResponseBody[201]}->asHttpResponseBodyFromParameter
    ]
};

HttpRouteBuilder->httpPost(^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.POST,
    pattern: #pattern,
    requestBody: EmptyRequestBody[]->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {NoResponseBody[204]}->asHttpResponseBodyFromParameter
];

HttpRouteBuilder->httpPostJson(^[pattern: RoutePattern, handler: Type<^Nothing => Any>, bodyArgName: String<1..>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.POST,
    pattern: #pattern,
    requestBody: {JsonRequestBody[#bodyArgName]}->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {NoResponseBody[204]}->asHttpResponseBodyFromParameter
];

HttpRouteBuilder->httpPutJson(^[pattern: RoutePattern, handler: Type<^Nothing => Any>, bodyArgName: String<1..>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.PUT,
    pattern: #pattern,
    requestBody: {JsonRequestBody[#bodyArgName]}->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {NoResponseBody[204]}->asHttpResponseBodyFromParameter
];

HttpRouteBuilder->httpPatchJson(^[pattern: RoutePattern, handler: Type<^Nothing => Any>, bodyArgName: String<1..>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.PATCH,
    pattern: #pattern,
    requestBody: {JsonRequestBody[#bodyArgName]}->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {NoResponseBody[204]}->asHttpResponseBodyFromParameter
];

HttpRouteBuilder->httpDelete(^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.DELETE,
    pattern: #pattern,
    requestBody: EmptyRequestBody[]->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {NoResponseBody[204]}->asHttpResponseBodyFromParameter
];

HttpRouteBuilder->httpGetAsJson(^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.GET,
    pattern: #pattern,
    requestBody: EmptyRequestBody[]->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {JsonResponseBody[200]}->asHttpResponseBodyFromParameter
];

HttpRouteBuilder->httpGetAsText(^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.GET,
    pattern: #pattern,
    requestBody: EmptyRequestBody[]->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {ContentResponseBody[200, 'text/plain']}->asHttpResponseBodyFromParameter
];

HttpRouteBuilder->httpGetAsHtml(^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute) :: HttpRoute[
    method: HttpRequestMethod.GET,
    pattern: #pattern,
    requestBody: EmptyRequestBody[]->asHttpRequestBodyToParameter,
    handler: #handler,
    response: {ContentResponseBody[200, 'text/html']}->asHttpResponseBodyFromParameter
];
