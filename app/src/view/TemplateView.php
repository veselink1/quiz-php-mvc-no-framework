<?php

require_once __DIR__ . '/ViewInterface.php';
require_once __DIR__ . '/ViewUtilities.php';

class TemplateView implements ViewInterface
{
    private $services;
    private $viewName;
    private $context;

    public function __construct($services, $viewName, $context = array())
    {
        $this->services = $services;
        $this->viewName = $viewName;
        $this->context = (object)$context;
    }

    public function renderView()
    {
        \ob_start();
        $returnValue = $this->renderFile();
        $content = \ob_get_clean();

        if ($returnValue !== 1)
        {
            if (!isset($returnValue['extends']) || !$returnValue['extends'])
            {
                throw new \Exception('Incorrect usage of return from view!');
            }

            $parentContext = (array)$this->context;
            $parentContext['content'] = $content;

            $parentView = new TemplateView($this->services, $returnValue['extends'], $parentContext);
            return $parentView->renderView();
        }
        else
        {
            return $content;
        }
    }

    private function renderFile()
    {
        // Make $context directly visible in template
        $context = $this->context;
        $tools = new ViewUtilities($this->services);
        return require __DIR__ . '/templates/' . $this->viewName . '.phtml';
    }

}
