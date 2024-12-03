module demo-route %% http-core:

EmptyRequestBody = :[];
EmptyRequestBody->toParameter(^HttpRequest => Map<Nothing, 0..0>) :: [:];

JsonRequestBody = $[valueKey: String];
JsonRequestBody->toParameter(^HttpRequest => Result<Map<JsonValue>, InvalidJsonString>) :: {
    request = #;
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

NoResponseBody = $[statusCode: HttpStatusCode];
NoResponseBody->fromParameter(^Any => HttpResponse) :: [
     statusCode: $statusCode,
     protocolVersion: HttpProtocolVersion.HTTP11,
     headers: [:],
     body: null
];

RedirectResponseBody = $[statusCode: HttpStatusCode];
RedirectResponseBody->fromParameter(^Any => Result<HttpResponse, Any>) :: {
    redirectValue = #=>as(type{String});
    [
         statusCode: $statusCode,
         protocolVersion: HttpProtocolVersion.HTTP11,
         headers: [:]->withKeyValue[key: 'Location', value: [redirectValue]],
         body: null
    ]
};

JsonResponseBody = $[statusCode: HttpStatusCode];
JsonResponseBody->fromParameter(^Any => Result<HttpResponse, Any>) :: {
    result = #;
    jsonValue = result->asJsonValue;
    ?whenTypeOf(jsonValue) is {
        type{Result<Nothing, InvalidJsonValue>}: jsonValue,
        type{JsonValue}: [
             statusCode: $statusCode,
             protocolVersion: HttpProtocolVersion.HTTP11,
             headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
             body: jsonValue->stringify
        ]
    }
};

RoutePattern <: String;
RoutePatternDoesNotMatch = :[];

HttpRouteDoesNotMatch = :[];
HttpRoute = $[
    method: HttpRequestMethod,
    pattern: RoutePattern,
    requestBody: JsonRequestBody|EmptyRequestBody,
    handler: Type<^Nothing => Any>,
    response: JsonResponseBody|NoResponseBody|RedirectResponseBody
];

HttpRoute->handleRequest(^HttpRequest => Result<HttpResponse, HttpRouteDoesNotMatch>) %% DependencyContainer :: {
    request = #;

    err = ^Any => Result<HttpResponse, HttpRouteDoesNotMatch> :: {
        httpResponse = #->as(type{HttpResponse});
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
                        bodyArg = $requestBody=>toParameter(request);
                        callParams = matchResult->mergeWith(bodyArg);
                        callParams = callParams->asJsonValue;
                        handlerType = $handler;
                        handlerParameterType = handlerType->parameterType;
                        handlerReturnType = handlerType->returnType;
                        handlerParams = callParams=>hydrateAs(handlerParameterType);
                        handlerInstance = %=>valueOf(handlerType);
                        handlerResult = ?noError(handlerInstance(handlerParams));
                        $response=>fromParameter(handlerResult)
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
HttpRouteChain->handleRequest(^HttpRequest => Result<HttpResponse, HttpRouteDoesNotMatch>) :: {
    request = #;
    routes = $routes;

    h = ^Array<HttpRoute, 1..> => Result<HttpResponse, HttpRouteDoesNotMatch> :: {
        routes = #;
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

httpPostJsonLocation = ^[pattern: RoutePattern, handler: Type<^Nothing => Any>, bodyArgName: String<1..>|Null] => HttpRoute :: {
    a = #.bodyArgName;
    requestBody = ?whenTypeOf(a) is {
        type{String}: JsonRequestBody[a],
        ~: EmptyRequestBody[]
    };
    HttpRoute[
        method: HttpRequestMethod.POST,
        pattern: #.pattern,
        requestBody: requestBody,
        handler: #.handler,
        response: RedirectResponseBody[201]
    ]
};

httpPost = ^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute :: HttpRoute[
    method: HttpRequestMethod.POST,
    pattern: #.pattern,
    requestBody: EmptyRequestBody[],
    handler: #.handler,
    response: NoResponseBody[204]
];

httpPostJson = ^[pattern: RoutePattern, handler: Type<^Nothing => Any>, bodyArgName: String<1..>] => HttpRoute :: HttpRoute[
    method: HttpRequestMethod.POST,
    pattern: #.pattern,
    requestBody: JsonRequestBody[#.bodyArgName],
    handler: #.handler,
    response: NoResponseBody[204]
];

httpPutJson = ^[pattern: RoutePattern, handler: Type<^Nothing => Any>, bodyArgName: String<1..>] => HttpRoute :: HttpRoute[
    method: HttpRequestMethod.PUT,
    pattern: #.pattern,
    requestBody: JsonRequestBody[#.bodyArgName],
    handler: #.handler,
    response: NoResponseBody[204]
];

httpPatchJson = ^[pattern: RoutePattern, handler: Type<^Nothing => Any>, bodyArgName: String<1..>] => HttpRoute :: HttpRoute[
    method: HttpRequestMethod.PATCH,
    pattern: #.pattern,
    requestBody: JsonRequestBody[#.bodyArgName],
    handler: #.handler,
    response: NoResponseBody[204]
];

httpDelete = ^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute :: HttpRoute[
    method: HttpRequestMethod.DELETE,
    pattern: #.pattern,
    requestBody: EmptyRequestBody[],
    handler: #.handler,
    response: NoResponseBody[204]
];

httpGetAsJson = ^[pattern: RoutePattern, handler: Type<^Nothing => Any>] => HttpRoute :: HttpRoute[
    method: HttpRequestMethod.GET,
    pattern: #.pattern,
    requestBody: EmptyRequestBody[],
    handler: #.handler,
    response: JsonResponseBody[200]
];