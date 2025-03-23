module $http/router %% $http/message, $http/middleware:

HttpLookupRouterPath = [path: String, type: Type, method: ?HttpRequestMethod];
HttpLookupRouterMapping = Array<HttpLookupRouterPath>;

HttpLookupRouter = $[routerMapping: HttpLookupRouterMapping];
HttpLookupRouter ==> HttpMiddleware %% [~DependencyContainer, ~HttpResponseBuilder] :: {
    run = ^[request: {HttpRequest}, type: Type] => {HttpResponse} :: {
        handler = %dependencyContainer->valueOf(#type);
        rh = handler->as(`HttpRequestHandler);
        ?whenIsError(rh) {
            %httpResponseBuilder(500)->withBody('Invalid handler type: ' + #type->asString + ' handler: ' + handler->printed + ', error: ' + rh->printed)
        } ~ {
            rh(#request->shape(`HttpRequest))
        };
    };

    ^[request: {HttpRequest}, handler: {HttpRequestHandler}] => {HttpResponse} :: {
        request = #request->shape(`HttpRequest);
        withUpdatedRequestPath = ^path: String => HttpRequest ::
            request->withKeyValue[key: 'target', value: request.target->substringRange[start: path->length, end: 9999]];
        kv = $routerMapping->findFirst(
            ^routerPath: HttpLookupRouterPath => Boolean :: {
                requestMethod = request.method;
                pathMethod = routerPath.method;
                request.target->startsWith(routerPath.path) &&
                    requestMethod == ?whenIsError(pathMethod) { requestMethod }
            }
        );
        ?whenTypeOf(kv) is {
            type{HttpLookupRouterPath}: run[request: withUpdatedRequestPath(kv.path), type: kv.type],
            ~: #handler->shape(`HttpRequestHandler)(request)
        }
    }
};
