module $http/bundle/composite-router %% $http/middleware/cors,
    $http/request-handler/not-found, $http/request-handler/composite, $http/router:

==> HttpCompositeRequestHandler %% [
    ~HttpNotFoundHandler,
    ~HttpLookupRouter,
    ~HttpCorsMiddleware
] :: HttpCompositeRequestHandler[
    defaultHandler: %httpNotFoundHandler,
    middlewares: [%httpCorsMiddleware, %httpLookupRouter]
];
