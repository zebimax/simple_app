<?php

abstract class Controller
{
    protected $action;
    protected $view;
    public function __construct($action = 'index')
    {
        $this->action = $action;
        if (method_exists($this, 'preDispatch')) {
            $this->preDispatch();
        }
    }

    /**
     * Renders a view.
     *
     * @param array    $parameters An array of parameters to pass to the view
     * @param string   $view       The view name
     * @param string   $laout      Layout to use
     * 
     */
    public function render(array $parameters = array(), $view = '', $layout = 'main')
    {
        if ($view) {
            $this->view = MVC_VIEW_DIR . str_replace(':', DIRECTORY_SEPARATOR, $view) . '.phtml';
        }
        extract($parameters);
        require MVC_LAYOUTS_DIR . $layout . '.phtml';
    }
}
