<?php
namespace MercadoPago;

use Exception;

/**
 * MercadoPago cURL RestClient
 */
class RestClient
{
    protected static $verbArray = [
        'get'    => 'GET',
        'post'   => 'POST',
        'put'    => 'PUT',
        'delete' => 'DELETE'
    ];

    protected $httpRequest = null;
    protected static $defaultParams = [];
    protected $customParams = [];

    public function __construct()
    {
        $this->httpRequest = new Http\CurlRequest();
    }

    protected function setHeaders(Http\HttpRequest $connect, $customHeaders)
    {
        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'MercadoPago DX-PHP SDK/ v' . Version::$_VERSION,
            'x-product-id' => 'BC32A7RU643001OI3940',
            'x-tracking-id' => 'platform:' . PHP_MAJOR_VERSION .'|' . PHP_VERSION . ',type:SDK' . Version::$_VERSION . ',so;'
        ];

        $defaultHeaders = array_merge($defaultHeaders, $customHeaders ?? []);

        if (!isset($defaultHeaders['Authorization'])) {
            $defaultHeaders['Authorization'] = 'Bearer '. SDK::getAccessToken();
        }

        $headers = [];
        foreach ($defaultHeaders as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        $connect->setOption(CURLOPT_HTTPHEADER, $headers);
    }

    protected function setData(Http\HttpRequest $connect, $data, $contentType = '')
    {
        if ($contentType == "application/json") {
            if (is_string($data)) {
                json_decode($data, true); // Validate JSON syntax
            } else {
                $data = json_encode($data); // Encode data to JSON
            }

            if (function_exists('json_last_error') && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON Error [". json_last_error() ."] - Data: ". $data);
            }
        }

        $connect->setOption(CURLOPT_POSTFIELDS, $data ?? "");
    }

    protected function exec($options)
    {
        $method = key($options);
        $requestPath = reset($options);
        $verb = self::$verbArray[$method];
    
        $headers = $options['headers'] ?? [];
        $urlQuery = $options['url_query'] ?? [];
        $formData = $options['form_data'] ?? null;
        $jsonData = $options['json_data'] ?? null;
    
        $defaultHttpParams = self::$defaultParams;
        $connectionParams = array_merge($defaultHttpParams, $this->customParams);
    
        $query = http_build_query($urlQuery);
        $uri = $connectionParams['address'] . $requestPath;
    
        if ($query !== '') {
            $uri .= (parse_url($uri, PHP_URL_QUERY) ? '&' : '?') . $query;
        }
    
        $connect = $this->getHttpRequest();
        $connect->setOption(CURLOPT_URL, $uri);
        $connect->setOption(CURLOPT_RETURNTRANSFER, true);
        $connect->setOption(CURLOPT_CUSTOMREQUEST, $verb);
    
        $this->setHeaders($connect, $headers);
    
        if (!empty($connectionParams['proxy_addr'])) {
            $connect->setOption(CURLOPT_PROXY, $connectionParams['proxy_addr']);
            $connect->setOption(CURLOPT_PROXYPORT, $connectionParams['proxy_port']);
        }
    
        // Configuração SSL
        if (isset($connectionParams['use_ssl']) && $connectionParams['use_ssl']) {
            $connect->setOption(CURLOPT_SSL_VERIFYPEER, true);
            $connect->setOption(CURLOPT_SSL_VERIFYHOST, 2); // Verificação rigorosa de certificado
            if (!empty($connectionParams['ca_file'])) {
                $connect->setOption(CURLOPT_CAINFO, $connectionParams['ca_file']);
            }
        } else {
            $connect->setOption(CURLOPT_SSL_VERIFYPEER, false); // Desativar verificação SSL
            $connect->setOption(CURLOPT_SSL_VERIFYHOST, false); // Desativar verificação do host SSL
        }
    
        $connect->setOption(CURLOPT_FOLLOWLOCATION, true);
    
        if ($formData !== null) {
            $this->setData($connect, $formData);
        }
    
        if ($jsonData !== null) {
            $this->setData($connect, $jsonData, "application/json");
        }
    
        $apiResult = $connect->execute();
        $apiHttpCode = $connect->getInfo(CURLINFO_HTTP_CODE);
    
        if ($apiResult === false) {
            throw new Exception($connect->error());
        }
    
        if ($apiHttpCode !== 200 && $apiHttpCode !== 201) {
            error_log($apiResult); // Log error response
        }
    
        return ['code' => $apiHttpCode, 'body' => json_decode($apiResult, true)];
    }
    
    public function get($uri, $options = [])
    {
        return $this->exec(['get' => $uri] + $options);
    }

    public function post($uri, $options = [])
    {
        return $this->exec(['post' => $uri] + $options);
    }

    public function put($uri, $options = [])
    {
        return $this->exec(['put' => $uri] + $options);
    }

    public function delete($uri, $options = [])
    {
        return $this->exec(['delete' => $uri] + $options);
    }

    public function setHttpParam($param, $value)
    {
        self::$defaultParams[$param] = $value;
    }

    protected function getArrayValue($array, $key)
    {
        return $array[$key] ?? false;
    }

    public function setHttpRequest($request)
    {
        $this->httpRequest = $request;
    }

    public function getHttpRequest()
    {
        return $this->httpRequest;
    }
}
