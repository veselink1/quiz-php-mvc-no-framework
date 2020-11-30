<?php

require_once __DIR__ . '/ViewInterface.php';
require_once __DIR__ . '/ViewUtilities.php';

class TemplateView implements ViewInterface
{
    private $viewName;
    private $context;

    public function __construct($viewName, $context = array())
    {
        $this->viewName = $viewName;
        $this->context = (object)$context;
    }

    public function renderView()
    {
        $this->blocks = [];

        \ob_start();
        $returnValue = $this->renderFile();
        $content = \ob_get_clean();

        if ($returnValue !== 1)
        {
            if (!isset($returnValue['extends']) || !$returnValue['extends'])
            {
                throw new \Exception('Incorrect usage of return from view!');
            }

            $parentView = new TemplateView($returnValue['extends'], ['content' => $content]);
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
        $tools = new ViewUtilities();
        return require __DIR__ . '/templates/' . $this->viewName . '.phtml';
    }

}
