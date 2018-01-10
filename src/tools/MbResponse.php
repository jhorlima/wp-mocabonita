<?php

namespace MocaBonita\tools;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Response;
use Illuminate\Support\Debug\Dumper;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Main class of the MocaBonita Response
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
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
     *
     * @throws MbException
     */
    public function setContent($content)
    {
        if (is_null($this->mbRequest)) {
            return $this;
        }

        $this->prepare($this->mbRequest);

        if ($this->mbRequest->isMethod("GET") || $this->mbRequest->isMethod("DELETE")) {
            $this->statusCode = BaseResponse::HTTP_OK;
        } elseif ($this->mbRequest->isMethod("POST") || $this->mbRequest->isMethod("PUT")) {
            $this->statusCode = BaseResponse::HTTP_CREATED;
        } else {
            $this->statusCode = BaseResponse::HTTP_RESET_CONTENT;
        }

        if ($content instanceof \Exception) {
            $this->statusCode = $content->getCode();
            $this->statusCode = $this->isSuccessful() ? $this->statusCode : BaseResponse::HTTP_BAD_REQUEST;
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
            wp_send_json($this->original);
        } else {
            parent::sendContent();
        }
    }

    /**
     * Redirect a page
     *
     * @param string $url
     * @param array  $params
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
            return $this->ajaxContent(new \Exception("No valid content has been submitted!", BaseResponse::HTTP_BAD_REQUEST));
        } elseif ($content instanceof \Exception) {

            $this->setStatusCode($content->getCode() < 300 ? BaseResponse::HTTP_BAD_REQUEST : $content->getCode());

            $message = $content->getMessage();

            if ($content instanceof MbException) {
                $content = $content->getMessages();
            } else {
                $content = null;
            }
        }

        $this->original = [
            'meta' => [
                'code'    => $this->getStatusCode(),
                'message' => $message,
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
     * @throws MbException
     */
    protected function htmlContent($content)
    {
        if ($content instanceof \Exception) {
            if ($this->getMbRequest()->isBlogAdmin()) {
                $this->adminNotice($content->getMessage(), 'error');
            } else {
                $this->original = "<strong>Erro:</strong> {$content->getMessage()}<br>";
            }
        } elseif ($content instanceof \SplFileInfo && !$this->getMbRequest()->isBlogAdmin()) {
            $this->downloadFile($content);
        } elseif (!is_string($content) && !$content instanceof Renderable) {
            ob_start();
            (new Dumper)->dump($content);
            $this->original = ob_get_contents();
            ob_end_clean();
        } else {
            $this->original = $content;
        }

        parent::setContent($this->original);
    }

    /**
     * Download file
     *
     * @param \SplFileInfo $content
     *
     * @throws MbException
     */
    public function downloadFile(\SplFileInfo $content)
    {
        if ($content->isFile()) {
            if (!$content->isReadable()) {
                throw new MbException("The download file can not be read!");
            }
            $finfo = new \finfo;
            header("Content-Type: {$finfo->file( $content->getRealPath(), FILEINFO_MIME)}");
            header("Content-Length: {$content->getSize()}");
            header("Content-Disposition: attachment; filename={$content->getBasename()}");
            readfile($content->getRealPath());
            exit();
        } else {
            throw new MbException("The requested file for download is invalid!");
        }
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
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return MbResponse
     */
    public static function create($content = '', $status = 200, $headers = [])
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
        return "<div class='notice notice-{$type} is-dismissible'><p><strong>{$message}</strong></p></div>";
    }
}