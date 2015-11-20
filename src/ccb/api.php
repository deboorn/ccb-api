<?php namespace CCB;

/**
 * Church Community Builder Api Wrapper
 * - This package is a very lightweight api wrapper for CCB and PHP 5+.
 * - cURL is not required for usage of this package.
 * - Enjoy!
 *
 * @package deboorn/ccb-api
 * @copyright Daniel Boorn <daniel.boorn@gmail.com>
 * @license Creative Commons Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0)
 */


/**
 * Class Exception
 *
 * @package CCB
 */
class Exception extends \Exception
{
    /**
     * @var null
     */
    protected $xml;

    /**
     * @param null $message
     * @param int $code
     * @param null $xml
     * @param Exception|null $previous
     */
    public function __construct($message = null, $code = 0, $xml = null, Exception $previous = null)
    {
        $this->xml = $xml;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return null
     */
    public function getXml()
    {
        return $this->xml;
    }
}


/**
 * Class Api
 *
 * Church Community Builder Api Wrapper
 * See readme.md for examples of use
 *
 * @package CCB
 */
class Api
{
    protected $username, $password, $apiUri;

    /**
     * @param string $username
     * @param string $password
     * @param string $apiUri
     */
    public function __construct($username, $password, $apiUri)
    {
        $this->username = $username;
        $this->password = $password;
        $this->apiUri = $apiUri;
    }

    /**
     * Returns basic authorization header
     *
     * @return string
     */
    protected function getAuthHeader()
    {
        return sprintf('Authorization: Basic %s', base64_encode("$this->username:$this->password"));
    }

    /**
     * Fetch api endpoint response
     *
     * @param $endpoint
     * @param string $data
     * @param string $verb
     * @return mixed
     * @throws Exception
     */
    public function fetch($endpoint, $data = "", $verb = 'GET')
    {

        $queryStr = "";
        if ($data instanceof \phpQuery) {
            $data = (string)$data;
        } else if (is_array($data)) {
            $queryStr = http_build_query($data);
            $data = "";
        }

        $context = stream_context_create(array(
            'http' => array(
                'method'  => $verb,
                'header'  => $this->getAuthHeader(),
                'content' => $data,
            )
        ));

        $url = "{$this->apiUri}?srv={$endpoint}&{$queryStr}";
        $xml = file_get_contents($url, false, $context);
        $response = \phpQuery::newDocumentXML($xml);

        if ($response['error']->length > 0) {
            throw new Exception($response['error']->text(), $response['error']->attr('number'), (string)$response);
        }

        return $response;
    }

    /**
     * Get xml model from xml template file
     *
     * @param $name
     * @return \phpQueryObject
     */
    public function fromTemplate($name)
    {
        $template = basename(__DIR__) . '/templates/' . $name . '.xml';
        return \phpQuery::newDocumentFileXML($template);
    }


}

