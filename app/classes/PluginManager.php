<?php

class PluginManager
{
    private $plugins;

    public function __construct(array $config)
    {
        $this->plugins = $config;
    }

    /**
     * @param $plugin
     * @return bool
     * @throws Exception
     */
    public function get($plugin)
    {
        if (
            isset($this->plugins[$plugin]) &&
            (
                is_string($this->plugins[$plugin]) ||
                is_object($this->plugins[$plugin]) ||
                is_array($this->plugins[$plugin])
            )
        ) {
            if (is_string($this->plugins[$plugin])) {
                $this->plugins[$plugin] = new $this->plugins[$plugin]();
            } elseif (
                is_array($this->plugins[$plugin]) &&
                isset($this->plugins[$plugin]['initializer']) &&
                is_callable($this->plugins[$plugin]['initializer'])
            ) {
                $this->plugins[$plugin] = $this->plugins[$plugin]['initializer']();
            }
        } else {
            throw new Exception('plugin must be string or have callable initializer');
        }

        return $this->plugins[$plugin];
    }
}