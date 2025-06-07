module $http/request-handler/composite %% $http/request-handler, $http/middleware:

HttpCompositeRequestHandler = #[defaultHandler: {HttpRequestHandler}, middlewares: Array<{HttpMiddleware}>];
HttpCompositeRequestHandler ==> HttpRequestHandler :: {
    ^request: {HttpRequest} => {HttpResponse} :: {
        ?whenTypeOf($middlewares) is {
            `Array<{HttpMiddleware}, 1..>: {
                m = $middlewares => withoutFirst;
                {m.element->shape(`HttpMiddleware)} [
                    request: request,
                    handler: HttpCompositeRequestHandler[$defaultHandler, m.array]
                ]
            },
            ~: $defaultHandler->shape(`HttpRequestHandler)(request)
        }
    }
};