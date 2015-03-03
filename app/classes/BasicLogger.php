<?php

use BasicLogger\Writers\WriterInterface;

class BasicLogger
{

    const EMERG  = 0;
    const ALERT  = 1;
    const CRIT   = 2;
    const ERR    = 3;
    const WARN   = 4;
    const NOTICE = 5;
    const INFO   = 6;
    const DEBUG  = 7;
    /**
     * @var WriterInterface[]
     */
    private $writers;

    /**
     * @param array $writers
     */
    public function __construct(array $writers = array())
    {
        foreach ($writers as $writer) {
            if ($writer instanceof WriterInterface) {
                $this->writers[] = $writer;
            }
        }
    }

    /**
     * @param array $message
     * @param int $priority
     */
    public function log(array $message, $priority = self::INFO)
    {
        $message['priority'] = (int)$priority;
        foreach ($this->writers as $writer) {
            $writer->write($message);
        }
    }

    public function info(array $message)
    {
        $this->log($message);
    }
}