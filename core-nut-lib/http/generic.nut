module $http/generic %% $http/middleware:

EntryPoint = $[~HttpRequestHandler];
==> LookupRouter %% LookupRouterMapping :: LookupRouter[routerMapping: %];
==> CompositeHandler %% [
    defaultHandler: NotFoundHandler,
    ~LookupRouter
] :: CompositeHandler[
    defaultHandler: %.defaultHandler->as(type{HttpRequestHandler}),
    middlewares: [
        %.lookupRouter->as(type{HttpMiddleware})
    ]
];
==> EntryPoint %% CompositeHandler :: EntryPoint[
    httpRequestHandler: %->as(type{HttpRequestHandler})
];
EntryPoint->handle(^~HttpRequest => Result<HttpResponse, Any>) :: $httpRequestHandler[httpRequest];

HttpServer = :[];
HttpServer->handleRequest(^~HttpRequest => Result<HttpResponse, Any>) %% EntryPoint :: %->handle(httpRequest);
