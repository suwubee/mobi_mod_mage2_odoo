<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Repo\Odoo\Connector;

class Rest
{
    const DEF_TIMEOUT_SEC = 60;
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';

    /** @var  \Praxigento\Odoo\Repo\Odoo\Connector\Sub\Adapter adapter for PHP functions to be mocked in tests */
    protected $adapter;
    /** @var  string */
    protected $baseUri;
    /** @var  \Praxigento\Odoo\Fw\Logger\Odoo separate channel to log Odoo activity */
    protected $logger;
    /** @var  \Praxigento\Odoo\Repo\Odoo\Connector\Api\ILogin */
    protected $login;

    public function __construct(
        \Praxigento\Odoo\Fw\Logger\Odoo $logger,
        \Praxigento\Odoo\Repo\Odoo\Connector\Sub\Adapter $adapter,
        \Praxigento\Odoo\Repo\Odoo\Connector\Api\ILogin $login,
        \Praxigento\Odoo\Helper\Config $hlpConfig
    ) {
        $this->logger = $logger;
        $this->adapter = $adapter;
        $this->baseUri = $hlpConfig->getConnectUri();
        $this->login = $login;
    }

    /**
     * @param $params
     * @param $route
     * @param string $method
     * @param int $timeout
     * @return \Praxigento\Odoo\Repo\Odoo\Connector\Api\Data\ICover
     * @throws \Exception
     */
    public function request($params, $route, $method = self::HTTP_METHOD_POST, $timeout = self::DEF_TIMEOUT_SEC)
    {
        /** @var string $sessId get session ID (authenticate if needed) */
        $sessId = $this->login->getSessionId();
        /* encode request parameters as JSON string */
        $request = $this->adapter->encodeJson($params);
        $contextOpts = [
            'http' => [
                'method' => $method,
                'header' => "Content-Type: application/json\r\nCookie: session_id=$sessId",
                'timeout' => $timeout,
                'content' => $request
            ]
        ];
        $context = $this->adapter->createContext($contextOpts);
        $uri = $this->baseUri . $route;
        $this->logger->debug("Request URI:\t$uri");
        $jsonContextOpt = $this->adapter->encodeJson($contextOpts);
        $this->logger->debug("Request context:\t\n$jsonContextOpt");
        $contents = $this->adapter->getContents($uri, $context);
        if ($contents === false) {
            $msg = "Cannot request Odoo REST API ({$this->baseUri}).";
            //$msg .= " HTTP Response headers: $jsonHttpRespHeaders";
            $this->logger->critical($msg);
            throw new \Exception($msg);
        }
        $this->logger->debug("Response:\t\n$contents");
        $data = $this->adapter->decodeJson($contents);
        $result = new \Praxigento\Odoo\Repo\Odoo\Connector\Api\Data\Def\Cover($data);
        return $result;
    }
}