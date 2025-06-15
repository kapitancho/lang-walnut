module $http/autowire %% $http/message, $http/request-handler:

RoutePattern := #String;
RoutePatternDoesNotMatch := ();

HttpAutoWireRequestBodyToParameter = ^{HttpRequest} => Result<Map<JsonValue>, Any>;
HttpAutoWireResponseBodyFromParameter = ^Any => Result<{HttpResponse}, Any>;

HttpAutoWireRouteDoesNotMatch := ();
HttpAutoWireRoute := $[
    method: HttpRequestMethod,
    pattern: RoutePattern,
    requestBody: {HttpAutoWireRequestBodyToParameter},
    handler: Type<^Nothing => Any>,
    response: {HttpAutoWireResponseBodyFromParameter}
];
HttpAutoWireRoute->handleRequest(^request: {HttpRequest} => Result<{HttpResponse}, HttpAutoWireRouteDoesNotMatch>) %% DependencyContainer :: {
    err = ^result: Any => Result<{HttpResponse}, HttpAutoWireRouteDoesNotMatch> %% [~HttpResponseBuilder] :: {
        httpResponse = result->as(`HttpResponse);
        ?whenTypeOf(httpResponse) is {
            `{HttpResponse}: httpResponse,
            ~: %httpResponseBuilder(500)->withBody('Cannot handle error type: ' + #->type->asString)
        }
    };
    request = request->shape(`HttpRequest);
    runner = ^ => Result<{HttpResponse}, Any> :: {
        ?whenValueOf(request.method) is {
            $method: {
                matchResult = $pattern->matchAgainst(request.target);
                ?whenTypeOf(matchResult) is {
                    `Map<String|Integer<0..>>: {
                        bodyArg = $requestBody->shape(`HttpAutoWireRequestBodyToParameter)=>invoke(request);
                        callParams = matchResult->mergeWith(bodyArg);
                        callParams = callParams->as(`JsonValue);
                        handlerType = $handler;
                        handlerParameterType = handlerType->parameterType;
                        handlerReturnType = handlerType->returnType;
                        handlerParams = callParams=>hydrateAs(handlerParameterType);
                        handlerInstance = %=>valueOf(handlerType);
                        handlerResult = ?noError(handlerInstance(handlerParams));
                        $response->shape(`HttpAutoWireResponseBodyFromParameter)=>invoke(handlerResult)
                    },
                    ~: @HttpAutoWireRouteDoesNotMatch()
                }
            },
            ~: @HttpAutoWireRouteDoesNotMatch()
        }
    };
    runnerResult = runner(null);
    ?whenTypeOf(runnerResult) is {
        `Error<HttpAutoWireRouteDoesNotMatch>: runnerResult,
        `Error: err(runnerResult->error),
        `{HttpResponse}: runnerResult
    }
};

HttpAutoWireRequestHandler := $[routes: Array<HttpAutoWireRoute, 1..>];
HttpAutoWireRequestHandler ==> HttpRequestHandler %% [~HttpResponseBuilder] :: {
    ^request: {HttpRequest} => {HttpResponse} :: {
        h = ^routes: Array<HttpAutoWireRoute, 1..> => Result<{HttpResponse}, HttpAutoWireRouteDoesNotMatch> :: {
            split = routes->withoutFirst;
            route = split.element;
            rest = split.array;

            result = route->handleRequest(request);
            ?whenTypeOf(result) is {
                `{HttpResponse}: result,
                `Error<HttpAutoWireRouteDoesNotMatch>: {
                    ?whenTypeOf(rest) is {
                        `Array<HttpAutoWireRoute, 1..>: h(rest),
                        ~: result
                    }
                },
                ~: result
            }
        };
        resp = h($routes);
        ?whenTypeOf(resp) is {
            `{HttpResponse}: resp,
            `Error<HttpAutoWireRouteDoesNotMatch>: %httpResponseBuilder(404)->withBody('Route not found: ' + request->shape(`HttpRequest).target)
        }
    }
};
