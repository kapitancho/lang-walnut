module $http/request-handler/composite %% $http/request-handler, $http/middleware:

HttpCompositeRequestHandler := #[defaultHandler: {HttpRequestHandler}, middlewares: Array<{HttpMiddleware}>];
HttpCompositeRequestHandler ==> HttpRequestHandler :: {
    ^request: {HttpRequest} => {HttpResponse} :: {
        ?whenTypeOf($middlewares) {
            `Array<{HttpMiddleware}, 1..>: {
                var{element: middleware, array: remainingMiddlewares} = $middlewares->withoutFirst?;
                {middleware->shape(`HttpMiddleware)} [
                    request: request,
                    handler: HttpCompositeRequestHandler[$defaultHandler, remainingMiddlewares]
                ]
            },
            ~: $defaultHandler->shape(`HttpRequestHandler)(request)
        }
    }
};