<?php

namespace Model\Assembla;


use Exception;

class AssemblaApi
{
    const CONTENT_TYPE_JSON = 'application/json; charset=utf-8';
    private $curl;
    private $url;
    private $contentType = 'json';
    private $xApiKey;
    private $xApiSecret;
    private $contentTypeHeader;
    public function __construct(array $config)
    {
        $this->url = $config['url'];
        $this->xApiKey = $config['x-api-key'];
        $this->xApiSecret = $config['x-api-secret'];
        $this->contentTypeHeader = isset($config['content-type']) ? $config['content-type'] : self::CONTENT_TYPE_JSON;
    }

    /**
     * @param $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function get($method, array $params = array())
    {
        $methodName = "get{$method}";
        if (method_exists($this, $methodName)) {
            $this->initApiCurl();
            curl_setopt($this->curl, CURLOPT_POST, false);
            $result = $this->$methodName($params);
            $this->closeCurl();
            return $result;
        }
        throw new Exception(sprintf('Invalid get method %s', $methodName));
    }

    /**
     * @param $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function put($method, array $params = array())
    {
        $methodName = "put{$method}";
        if (method_exists($this, $methodName)) {
            $this->initApiCurl();
            curl_setopt($this->curl, CURLOPT_POST, false);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
            $result = $this->$methodName($params);
            $this->closeCurl();
            return $result;
        }
        throw new Exception(sprintf('Invalid put method %s', $methodName));
    }

    /**
     * @param $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function post($method, array $params = array())
    {
        $methodName = "post{$method}";
        if (method_exists($this, $methodName)) {
            $this->initApiCurl();
            curl_setopt($this->curl, CURLOPT_POST, true);
            $result = $this->$methodName($params);
            $this->closeCurl();
            return $result;
        }
        throw new Exception(sprintf('Invalid post method %s', $methodName));
    }

    /**
     * @param array $config
     * @return array
     * @throws Exception
     */
    private function getTickets(array $config)
    {
        $foundTickets = array();
        $range = isset($config['range']) ? (array)$config['range'] : array();
        if (isset($config['space'])) {
            $apiUrl = $this->url . "spaces/{$config['space']}/tickets/";
            foreach ($range as $ticketNumber) {
                curl_setopt($this->curl, CURLOPT_URL, $apiUrl . $ticketNumber);
                $ticket = $this->decode(curl_exec($this->curl));
                if (!$ticket || isset($ticket['error'])) {
                    echo sprintf('Error!Ticket %s not found!', $ticketNumber);
                } else {
                    $foundTickets[] = $ticket;
                }
            }
        }
        return $foundTickets;
    }

    /**
     * @param array $updates
     * @return int
     */
    private function putTickets(array $updates)
    {
        $updatedTickets = 0;
        $apiUrl = $this->url . "spaces/{$updates['space']}/tickets/";
        foreach ($updates['tickets'] as $ticket) {
            $updateParams = array_intersect_key(array_diff_assoc($updates['params'], $ticket), $ticket);
            if (!empty($updateParams)) {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->encode(array('ticket' => $updateParams)));
                curl_setopt($this->curl, CURLOPT_URL, "{$apiUrl}{$ticket['number']}.{$this->contentType}");
                curl_exec($this->curl);
                $curlGetInfo = curl_getinfo($this->curl);
                if ($curlGetInfo['http_code'] >= 200 && $curlGetInfo['http_code'] < 300) {
                    $updatedTickets++;
                }
            }
        }
        return $updatedTickets;
    }

    /**
     * @param array $post
     * @return int
     */
    private function postTicketsComments(array $post)
    {
        $posted = 0;
        if (isset($post['params']['comment']) && $post['params']['comment']) {
            $apiUrlComments = $this->url . "spaces/{$post['space']}/tickets/%d/ticket_comments.{$this->contentType}";
            foreach ($post['tickets'] as $ticket) {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->encode(
                    array('ticket_comment' =>
                        array('comment' => $post['params']['comment']))
                    )
                );
                curl_setopt($this->curl, CURLOPT_URL, sprintf($apiUrlComments, $ticket['number']));
                $postResult = curl_exec($this->curl);
                $postedComment = $this->decode($postResult);
                $curlGetInfo = curl_getinfo($this->curl);
                if ($curlGetInfo['http_code'] >= 200 && $curlGetInfo['http_code'] < 300) {
                    $posted++;
                } else {
                    echo "Error posting comment for ticket #{$ticket['number']}" . PHP_EOL;
                }
            }
        }

        return $posted;
    }

    private function initApiCurl()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            "X-Api-Key: {$this->xApiKey}",
            "X-Api-Secret: {$this->xApiSecret}",
            "Content-type: {$this->contentTypeHeader}"
        ));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    private function closeCurl()
    {
        curl_close($this->curl);
    }

    /**
     * @param $result
     * @return mixed|string
     */
    private function decode($result)
    {
        switch ($this->contentType) {
            case 'json' :
                return json_decode($result, true);
                break;
            default:
                return 'Decoding error!Unknown content-type';
                break;
        }
    }

    /**
     * @param array $data
     * @return string
     * @throws Exception
     */
    private function encode(array $data)
    {
        switch ($this->contentType) {
            case 'json' :
                return json_encode($data);
                break;
            default:
                throw new Exception('Encoding error!Unknown content-type');
                break;
        }
    }
}