<?php
    if( !session_id() ) @session_start();
    require '../vendor/autoload.php';

    use App\QueryBuilder;
    use League\Plates\Engine;
    use \Tamtamchik\SimpleFlash\Flash;
    use DI\ContainerBuilder;
    use Aura\SqlQuery\QueryFactory;
    use Delight\Auth\Auth;
    use Password\Validator;
    use Password\StringHelper;



    $builder = new ContainerBuilder;
    
    $builder->addDefinitions([
        PDO::class => function(){
            return new PDO('mysql:host=localhost;dbname=components;', 'root', 'root');
        },
        QueryFactory::class => function(){
            return new QueryFactory('mysql');
        },
        Engine::class => function(){
            return new Engine('../app/views');
        },
        Auth::class => function($container){
            return new Auth($container->get('PDO'));
        },
        Validator::class => function(){
            return new Validator(new StringHelper);
        }
    ]);
    $container = $builder->build();

    $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/create', ['App\Controllers\PagesController', 'create']);
        $r->addRoute('POST', '/create-act', ['App\Controllers\AdminController', 'create']);
        $r->addRoute('GET', '/create-act', ['App\Controllers\AdminController', 'create']);

        $r->addRoute('GET', '/setadmin/{id:\d+}', ['App\Controllers\AdminController', 'setadmin']);
        $r->addRoute('GET', '/deladmin/{id:\d+}', ['App\Controllers\AdminController', 'deladmin']);


        $r->addRoute('GET', '/edit/{id:\d+}', ['App\Controllers\PagesController', 'edit']);
        $r->addRoute('POST', '/edit-act/{id:\d+}', ['App\Controllers\EditController', 'edit']);

        $r->addRoute('GET', '/image/{id:\d+}', ['App\Controllers\PagesController', 'image']);
        $r->addRoute('POST', '/image-act/{id:\d+}', ['App\Controllers\EditController', 'image']);

        $r->addRoute('GET', '/profile/{id:\d+}', ['App\Controllers\PagesController', 'profile']);

        $r->addRoute('GET', '/security/{id:\d+}', ['App\Controllers\PagesController', 'security']);
        $r->addRoute('POST', '/email/{id:\d+}', ['App\Controllers\EditController', 'email']);
        $r->addRoute('GET', '/verification-edit', ['App\Controllers\EditController', 'verification']);
        $r->addRoute('POST', '/password/{id:\d+}', ['App\Controllers\EditController', 'password']);
        
        $r->addRoute('GET', '/status/{id:\d+}', ['App\Controllers\PagesController', 'status']);
        $r->addRoute('POST', '/status-act/{id:\d+}', ['App\Controllers\EditController', 'status']);

        $r->addRoute('GET', '/users', ['App\Controllers\PagesController', 'users']);
        $r->addRoute('GET', '/', ['App\Controllers\PagesController', 'users']);

        $r->addRoute('GET', '/register', ['App\Controllers\PagesController', 'register']);
        $r->addRoute('POST', '/registration', ['App\Controllers\AuthController', 'registration']);
        $r->addRoute('GET', '/registration', ['App\Controllers\AuthController', 'registration']);
        $r->addRoute('GET', '/verification', ['App\Controllers\AuthController', 'verification']);
        
        $r->addRoute('POST', '/auth', ['App\Controllers\AuthController', 'auth']);

        $r->addRoute('GET', '/logout', ['App\Controllers\AuthController', 'logout']);

        $r->addRoute('GET', '/login', ['App\Controllers\PagesController', 'login']);
        $r->addRoute('GET', '/test', ['App\Controllers\AuthController', 'test']);

        $r->addRoute('GET', '/delete/{id:\d+}', ['App\Controllers\EditController', 'delete']);


        




        $r->addRoute('GET', '/faker', ['App\Controllers\PagesController', 'faker']);
    });
    
    // Fetch method and URI from somewhere
    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    
    // Strip query string (?foo=bar) and decode URI
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);

    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            // ... 404 Not Found
            break;
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            // ... 405 Method Not Allowed
            break;
        case FastRoute\Dispatcher::FOUND:
            echo $container->call($routeInfo[1], $routeInfo[2]);
            break;
    }
?>