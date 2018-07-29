<?php

include_once AuWebRoot . '/src/back/utils/LocalizationHelpers.php';
include_once AuWebRoot . '/src/back/views/pages/NotFoundPage.php';
use Katzgrau\KLogger\Logger as Logger;

class Server
{
    private static $instance = null;

    private $logger = null;
    private $router = null;
    private $params = null;

    private function __construct($params)
    {
        $this->params = $params;

        $this->initializeConfig();
        $this->initializeLogger();
        $this->initializeLocalization();
    }

    public static function getInstance($params = [])
    {
        if (self::$instance == null) {
            self::$instance = new Server($params);
            self::$instance->initializeRouter();
        }
        return self::$instance;
    }

    public function initializeConfig()
    {
        $config = parse_ini_file('config/config.ini');
        define('AU_CONFIG', $config);
        $GLOBALS['config'] = $config;
    }

    private function initializeLogger()
    {
        $this->logger = new Logger(AU_CONFIG['log.file'], Psr\Log\LogLevel::DEBUG);
    }

    public function initializeLocalization()
    {
        $localization = parse_ini_file('config/messages.ini');
        $localization = LocalizationHelpers::parseAssocArrayDeeply($localization);
        define('Localization', $localization);
    }

    public function initializeRouter()
    {
        $this->router = new \Klein\Klein();
        $this->router->respond(function ($request, $response) {
            $start = microtime();
        });

        $this->router->respond(function ($request, $response, $service, $app) {
            // Handle exceptions => flash the message and redirect to the referrer
            $this->router->onError(function ($klein, $errorMessage, $type, $error) use ($response) {
                if ($error instanceof PageNotFoundError) {
                    $response->body((new NotFoundPage())->getHtml());
                    $response->code(HttpStatuses::NOT_FOUND);
                    $response->send();
                    return;
                } else if ($error instanceof BaseError) {
                    $response->code($error->status);
                    $response->json($error->toJson());
                    $this->logger->error(json_encode($error->toJson()));
                    return;
                }
                $internalError = new InternalError($error);
                $response->code(HttpStatuses::INTERNAL_SERVER_ERROR);
                $response->json($internalError->toJson());
                $this->logger->error($internalError);
            });
        });

        $routesPath = isset($params['routes']) ? $params['routes'] : AuWebRoot . '/src/back/routes/index.php';

        include_once $routesPath;

        $this->router->respond(function ($request, $response) {
            if (!$response->isLocked()) {
                throw new NotFoundError('Request uri \'' . $request->uri() . '\' not found.');
            }
        });
        $server = $this;
        $this->router->afterDispatch(function () use ($server) {
            //$this->logger->debug($request->method() . ' ' . $request->uri() . ' ' . $response->code() . ' ' . $request->userAgent());
        });
        $this->router->dispatch();
    }

    public function assertIsSecure(&$request) {
        if (!$request->isSecure()) {
            throw new NotSecuredConnectionError();
        }
    }

    public function redirectToSecure(&$response) {
        $response->status(HttpStatuses::MOVED_PERMANENTLY);
        $response->header('Location', 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    }

    public function router()
    {
        return $this->router;
    }

    public function logger()
    {
        return $this->logger;
    }
}
