<?php

namespace Application;

class Result
{
    const PARAM_SUCCESS = 'success';
    const PARAM_DATA = 'data';
    const PARAM_MESSAGE = 'message';

    protected $success;
    protected $data;
    protected $message;

    /**
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case self::PARAM_SUCCESS:
                        $this->success = $value;
                        break;
                    case self::PARAM_DATA:
                        $this->data = $value;
                        break;
                    case self::PARAM_MESSAGE:
                        $this->message = $value;
                        break;
                    default:
                        break;
                }
            }
        }
    }

    static public function create(array $params = array())
    {
        return new self($params);
    }

    /**
     * @param $success
     *
     * @return Result
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param $data
     * @return Result
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
