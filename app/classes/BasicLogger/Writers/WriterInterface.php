<?php

namespace BasicLogger\Writers;


interface WriterInterface
{
    public function write(array $message);
}