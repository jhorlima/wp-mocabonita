<?php

namespace MocaBonita\view;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbPath;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\MbResponse;

/**
 * Main class of the MocaBonita View
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\view
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbView implements View
{
    /**
     * Template name
     *
     * @var string
     */
    protected $template;

    /**
     * Name of the current page, respectively the name of the folder where the view is located
     *
     * @var string
     */
    protected $page;

    /**
     * Name of the current action, respectively the name of the view in the page folder
     *
     * @var string
     */
    protected $action;

    /**
     * Variables stored for use in view
     *
     * @var mixed[]
     */
    protected $attributes;

    /**
     * View content
     *
     * @var string
     */
    protected $content;

    /**
     * View file extension
     *
     * @var string
     */
    protected $extension;

    /**
     * View path
     *
     * @var string
     */
    protected $viewPath;

    /**
     * Stores the current MbRequest of the request
     *
     * @var MbRequest
     */
    protected $mbRequest;

    /**
     * Stores the current MbResponse of the response
     *
     * @var MbResponse
     */
    protected $mbResponse;

    /**
     * View construct.
     */
    public function __construct()
    {
        $this->setAttributes([]);
        $this->setExtension("phtml");
        $this->setViewPath(MbPath::pViewDir());
    }

    /**
     * Get template name
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template name
     *
     * @param string $template
     *
     * @return MbView
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get page name
     *
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set page name
     *
     * @param string $page
     *
     * @return MbView
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set action name
     *
     * @param string $action
     *
     * @return MbView
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get variables for view
     *
     * @param null $name
     *
     * @return mixed[]
     */
    public function getAttribute($name = null)
    {
        return is_null($name) ? $this->attributes : Arr::get($this->attributes, $name);
    }

    /**
     * Set variables for view
     *
     * @param string[] $attributes
     *
     * @return MbView
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set variable for view
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return MbView
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Get content view
     *
     * @return string
     */
    public function getContent()
    {
        return !is_null($this->content) ? $this->content : "No valid content has been submitted!";
    }

    /**
     * Set content view
     *
     * @param string $content
     *
     * @return MbView
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get view file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set view file extension
     *
     * @param string $extension
     *
     * @return MbView
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get view path
     *
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * Set view path
     *
     * @param string $viewPath
     *
     * @return MbView
     */
    public function setViewPath($viewPath)
    {
        $this->viewPath = $viewPath;

        return $this;
    }

    /**
     * Get MbRequest
     *
     * @return MbRequest
     */
    public function getMbRequest()
    {
        return $this->mbRequest;
    }

    /**
     * Set MbRequest to MbView
     *
     * @param MbRequest $mbRequest
     *
     * @return MbView
     */
    public function setMbRequest(MbRequest $mbRequest)
    {
        $this->mbRequest = $mbRequest;

        return $this;
    }

    /**
     * Get MbResponse
     *
     * @return MbResponse
     */
    public function getMbResponse()
    {
        return $this->mbResponse;
    }

    /**
     * Set MbResponse to MbView
     *
     * @param MbResponse $mbResponse
     *
     * @return MbView
     */
    public function setMbResponse(MbResponse $mbResponse)
    {
        $this->mbResponse = $mbResponse;

        return $this;
    }

    /**
     * Set parameters for view
     *
     * @param string $templateName
     * @param string $page
     * @param string $action
     * @param array  $attributes
     * @param string $extension
     *
     * @return MbView
     */
    public function setView($templateName, $page, $action, array $attributes = [], $extension = "phtml")
    {
        $this->setTemplate($templateName);
        $this->setPage($page);
        $this->setAction($action);
        $this->setAttributes($attributes);
        $this->setExtension($extension);

        return $this;
    }

    /**
     * Get File Full Path with extension
     *
     * @param string $type If the file either is a view or is a template
     *
     * @return string
     */
    protected function getFileFullPath($type = 'action')
    {
        if ($type == 'action') {
            return $this->viewPath . "{$this->page}/{$this->action}.{$this->extension}";
        } else {
            return $this->viewPath . "{$this->template}.{$this->extension}";
        }
    }

    /**
     * Get a piece to include in the view
     *
     * @param string $filePiece File part address
     *
     * @return void
     */
    public function piece($filePiece)
    {
        $filePiece = $this->viewPath . "{$filePiece}.{$this->extension}";

        if (file_exists($filePiece)) {
            include $filePiece;
        } else {
            MbException::registerError(new \Exception("The file {$filePiece} not found!"));
        }
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $viewPath = $this->getFileFullPath();
        $templatePath = $this->getFileFullPath('template');

        if (file_exists($viewPath)) {
            ob_start();
            include $viewPath;
            $this->setContent(ob_get_contents());
            ob_end_clean();
        } else {
            MbException::registerError(new \Exception("The file {$viewPath} not found!"));
        }

        if (file_exists($templatePath)) {
            ob_start();
            include $templatePath;
            $this->setContent(ob_get_contents());
            ob_end_clean();
        } else {
            MbException::registerError(new \Exception("The file {$templatePath} not found!"));
        }

        return $this->getContent();
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name()
    {
        return "{$this->getPage()}.{$this->getAction()}";
    }

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array $key
     * @param  mixed        $value
     *
     * @return $this
     */
    public function with($key, $value = null)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Dynamically retrieve attributes on the view.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the view.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
}