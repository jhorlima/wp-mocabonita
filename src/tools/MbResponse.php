<?php

namespace MocaBonita\tools;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Response;
use Illuminate\Support\Debug\Dumper;

/**
 * Main class of the MocaBonita Response
 *
 * @author Jhordan Lima <jhorlima@icloud.com>
 * @category WordPress
 * @package \MocaBonita\tools
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 * @version 3.1.0
 */
class MbResponse extends Response
{
    /**
     * Stores the current MbRequest of the request
     *
     * @var MbRequest
     */
    protected $mbRequest;

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
     * Set MbRequest to Controller
     *
     * @param MbRequest $mbRequest
     *
     * @return MbResponse
     */
    public function setMbRequest(MbRequest $mbRequest)
    {
        $this->mbRequest = $mbRequest;
        return $this;
    }

    /**
     * Set the content on the response.
     *
     * @param mixed $content
     *
     * @return MbResponse
     */
    public function setContent($content)
    {
        if (is_null($this->mbRequest)) {
            return $this;
        } elseif ($this->mbRequest->isMethod("GET") || $this->mbRequest->isMethod("DELETE")) {
            $this->statusCode = 200;
        } elseif ($this->mbRequest->isMethod("POST") || $this->mbRequest->isMethod("PUT")) {
            $this->statusCode = 201;
        } else {
            $this->statusCode = 204;
        }

        if ($content instanceof \Exception) {
            $this->statusCode = $content->getCode();
            $this->statusCode = $this->statusCode < 300 && $this->statusCode != 204 ? 400 : $this->statusCode;
        }

        if ($this->mbRequest->isAjax()) {
            $this->ajaxContent($content);
        } else {
            $this->htmlContent($content);
        }

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     */
    public function sendContent()
    {
        if ($this->mbRequest->isAjax()) {
            wp_send_json($this->original, $this->statusCode);
        } else {
            parent::sendContent();
        }
    }

    /**
     * Redirect a page
     *
     * @param string $url
     * @param array $params
     *
     */
    public function redirect($url, array $params = [])
    {
        if (!empty($params)) {
            $url = rtrim(preg_replace('/\?.*/', '', $url), '/');
            $url .= "?" . http_build_query($params);
        }

        header("Location: {$url}");
        exit();
    }

    /**
     * Format content for json output
     *
     * @param array|\Exception $content
     *
     * @return array[]
     */
    protected function ajaxContent($content)
    {
        $message = null;

        $this->header('Content-Type', 'application/json');

        if ($content instanceof Arrayable) {
            $content = $content->toArray();
        } elseif (is_string($content)) {
            $content = ['content' => $content];
        } elseif (!is_array($content) && !$content instanceof \Exception) {
            return $this->ajaxContent(new \Exception("No valid content has been submitted!", 204));
        } elseif ($content instanceof \Exception) {
            $this->setStatusCode($content->getCode() < 300 && $content->getCode() != 204 ? 400 : $content->getCode());
            $message = $content instanceof MbException ? $content->getWpErrorMessages() : $content->getMessage();
            $content = $content instanceof MbException ? $content->getExcepitonDataArray() : null;
        }

        $this->original = [
            'meta' => [
                'code' => $this->getStatusCode(),
                'message' => $message
            ],
            'data' => $content,
        ];

        return $this->original;

    }

    /**
     * Format content for html output
     *
     * @param $content
     *
     * @return void
     */
    protected function htmlContent($content)
    {
        if ($content instanceof \Exception) {
            if($this->getMbRequest()->isBlogAdmin()){
                $this->adminNotice($content->getMessage(), 'error');
                if($content instanceof MbException){
                    foreach ($content->getWpErrorMessages(true) as $errorMessage) {
                        $this->adminNotice($errorMessage, 'warning');
                    }
                }
            } else {
                $this->original = "<strong>Erro:</strong> {$content->getMessage()}<br>";
                if($content instanceof MbException){
                    foreach ($content->getWpErrorMessages(true) as $errorMessage) {
                        $this->original .= "{$errorMessage}<br>";
                    }
                }
            }
        } elseif (!is_string($content) && !$content instanceof Renderable) {
            ob_start();
            (new Dumper())->dump($content);
            $this->original = ob_get_contents();
            ob_end_clean();
        } else {
            $this->original = $content;
        }

        parent::setContent($this->original);
    }

    /**
     * Factory method for chainability.
     *
     * Example:
     *
     *     return Response::create($body, 200)
     *         ->setSharedMaxAge(300);
     *
     * @param mixed $content The response content, see setContent()
     * @param int $status The response status code
     * @param array $headers An array of response headers
     *
     * @return MbResponse
     */
    public static function create($content = '', $status = 200, $headers = array())
    {
        return new static($content, $status, $headers);
    }

    /**
     * Post an admin notice on the dashboard
     *
     * @param string $message
     * @param string $type
     *
     */
    public function adminNotice($message, $type = 'success')
    {
        MbWPActionHook::addActionCallback('admin_notices', function () use ($message, $type) {
            echo self::adminNoticeTemplate($message, $type);
        });
    }

    /**
     * Get admin notice structure template
     *
     * @param string $message
     * @param string $type
     *
     * @return string
     */
    public function adminNoticeTemplate($message, $type = 'error')
    {
        return "<div class='notice notice-{$type}'><p>{$message}</p></div>";
    }
}