<?php namespace CCB;


class Exception extends \Exception
{
    protected $xml;

    public function __construct($message = null, $code = 0, $xml = null, Exception $previous = null)
    {
        $this->xml = $xml;
        parent::__construct($message, $code, $previous);
    }

    public function getXml()
    {
        return $this->xml;
    }
}


class Api
{
    protected $username, $password, $apiUri;

    public function __construct($username, $password, $apiUri)
    {
        $this->username = $username;
        $this->password = $password;
        $this->apiUri = $apiUri;
    }

    protected function getAuthHeader()
    {
        return sprintf('Authorization: Basic %s', base64_encode("$this->username:$this->password"));
    }

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

    public function fromTemplate($name)
    {
        $template = basename(__DIR__) . '/templates/' . $name . '.xml';
        return \phpQuery::newDocumentFileXML($template);
    }


}

