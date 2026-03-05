module $http/middleware %% $http/message, $http/request-handler:

HttpMiddleware = ^[request: {HttpRequest}, handler: {HttpRequestHandler}] => {HttpResponse};
