<?php

namespace Plugins\Controller;


class LinkMaker
{
    public function __invoke(array $params = array())
    {
        $link = '';
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $link .= sprintf('&%s=%s', $key, $value);
            }
        }
        return $link;
    }
}