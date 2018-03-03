<?php

namespace MocaBonita\view;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
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
    use Macroable;

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
     * Stores the view full
     *
     * @var string
     */
    protected $viewFullPath;

    /**
     * Stores the template full
     *
     * @var string
     */
    protected $templateFullPath;

    /**
     * @param string $type
     * @param string|array $message
     *
     * @return MbView
     */
    public function addFlash($type, $message)
    {
        if (is_array($message)) {
            $this->getMbRequest()->getFlashBag()->set($type, $message);
        } else {
            $this->getMbRequest()->getFlashBag()->add($type, $message);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array  $default
     */
    public function getFlash($name, array $default = [])
    {
        $this->getMbRequest()->getFlashBag()->get($name, $default);
    }

    /**
     * @return array[]
     */
    public function getFlashes()
    {
        return $this->getMbRequest()->getFlashBag()->all();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFlash($name)
    {
        return $this->getMbRequest()->getFlashBag()->has($name);
    }

    /**
     * @param string|array $message
     *
     * @param string       $type
     *
     * @return MbView
     */
    public function successFlash($message, $type = 'success')
    {
        return $this->addFlash($type, $message);
    }

    /**
     * @param string|array $message
     *
     * @param string       $type
     *
     * @return MbView
     */
    public function errorFlash($message, $type = 'error')
    {
        return $this->addFlash($type, $message);
    }

    /**
     * @param string|array $message
     *
     * @param string       $type
     *
     * @return MbView
     */
    public function infoFlash($message, $type = 'info')
    {
        return $this->addFlash($type, $message);
    }

    /**
     * @param string|array $message
     *
     * @param string       $type
     *
     * @return MbView
     */
    public function warnFlash($message, $type = 'warn')
    {
        return $this->addFlash($type, $message);
    }

    /**
     * @param \Exception $exception
     *
     * @param string     $type
     *
     * @return MbView
     */
    public function exceptionFlash(\Exception $exception, $type = 'error')
    {
        if ($exception instanceof MbException) {
            $messages = $exception->getMessages();

            if (empty($messages)) {
                return $this->errorFlash($exception->getMessage(), $type);
            } else {
                $erros = [$exception->getMessage()];

                foreach ($messages as $item => $message) {
                    if (is_array($message)) {
                        foreach ($message as $erro) {
                            $erros[] = "<strong>{$item}</strong>: {$erro}";
                        }
                    } else {
                        $erros[] = $message;
                    }
                }

                return $this->errorFlash($erros, $type);
            }

        } else {
            return $this->errorFlash($exception->getMessage(), $type);
        }
    }

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
     * @return string
     */
    public function getViewFullPath()
    {
        return $this->viewFullPath;
    }

    /**
     * @param string $viewFullPath
     *
     * @return MbView
     */
    public function setViewFullPath($viewFullPath)
    {
        $this->viewFullPath = $viewFullPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateFullPath()
    {
        return $this->templateFullPath;
    }

    /**
     * @param string $templateFullPath
     *
     * @return MbView
     */
    public function setTemplateFullPath($templateFullPath)
    {
        $this->templateFullPath = $templateFullPath;

        return $this;
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
        $this->setViewFullPath($this->getFileFullPath());
        $this->setTemplateFullPath($this->getFileFullPath('template'));

        if (file_exists($this->getViewFullPath())) {
            ob_start();
            include $this->getViewFullPath();
            $this->setContent(ob_get_contents());
            ob_end_clean();
        } else {
            MbException::registerError(new \Exception("The file {$this->getViewFullPath()} not found!"));
        }

        if (file_exists($this->getTemplateFullPath())) {
            ob_start();
            include $this->getTemplateFullPath();
            $this->setContent(ob_get_contents());
            ob_end_clean();
        } else {
            MbException::registerError(new \Exception("The file {$this->getTemplateFullPath()} not found!"));
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