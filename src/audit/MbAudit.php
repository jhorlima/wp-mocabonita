<?php

namespace MocaBonita\audit;

use Illuminate\Contracts\Support\Arrayable;
use MocaBonita\MocaBonita;
use MocaBonita\model\MbWpUser;
use MocaBonita\tools\eloquent\MbDatabase;
use MocaBonita\tools\MbRequest;
use MocaBonita\view\MbView;

/**
 * Main class of the MocaBonita Audit
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools
 *
 * @copyright Jhordan Lima 2018
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbAudit implements Arrayable
{

    /**
     * Current user
     *
     * @var MbWpUser
     */
    protected $user;

    /**
     * Current request
     *
     * @var MbRequest
     */
    protected $request;

    /**
     * Current response data
     *
     * @var array
     */
    protected $requestData;

    /**
     * Response Type
     *
     * @var string
     */
    protected $responseType;

    /**
     * Current response data
     *
     * @var mixed
     */
    protected $responseData;

    /**
     * Current response header
     *
     * @var array[]
     */
    protected $responseHeader;

    /**
     * Current response status code
     *
     * @var int
     */
    protected $responseStatusCode;

    /**
     * to record data only on the pages of Mocabonita
     *
     * @var boolean
     */
    protected $onlyMbPage;

    /**
     * Store response html (When available)
     *
     * @var boolean
     */
    protected $storeView;

    /**
     * Store callback
     *
     * @var callable|array
     */
    protected $storage;

    /**
     * Store MocaBonita instance
     *
     * @var MocaBonita
     */
    protected $mocaBonita;

    /**
     * @return array[]
     */
    public function getUser()
    {
        return $this->user->toArray();
    }

    /**
     * @param MbWpUser $user
     *
     * @return MbAudit
     */
    public function setUser(MbWpUser $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getRequest()
    {
        if (!is_array($this->requestData)) {
            $this->requestData = [
                'query'  => $this->request->query->all(),
                'server' => $this->request->server->all(),
                'header' => $this->request->headers->all(),
                'source' => $this->request->inputSource(),
                'files'  => $this->request->files->all(),
                'method' => $this->request->method(),
                'ip'     => $this->request->getClientIp(),
                'path'   => $this->request->getPathInfo(),
            ];
        }

        return $this->requestData;
    }

    /**
     * @param MbRequest $request
     *
     * @return MbAudit
     */
    public function setRequest(MbRequest $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponseType()
    {
        return $this->responseType;
    }

    /**
     * @param string $responseType
     *
     * @return MbAudit
     */
    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponseData()
    {
        if ($this->responseData instanceof MbView) {

            if ($this->isStoreView()) {

                $this->setResponseData([
                    'content'    => $this->responseData->getContent(),
                    'view'       => $this->responseData->name(),
                    'template'   => $this->responseData->getTemplate(),
                    'path'       => [
                        'view'     => $this->responseData->getViewFullPath(),
                        'template' => $this->responseData->getTemplateFullPath(),
                    ],
                    'attributes' => $this->responseData->getAttribute(),
                ]);

            } else {

                $this->setResponseData([
                    'view'     => $this->responseData->name(),
                    'template' => $this->responseData->getTemplate(),
                    'path'     => [
                        'view'     => $this->responseData->getViewFullPath(),
                        'template' => $this->responseData->getTemplateFullPath(),
                    ],
                ]);
            }

        } elseif ($this->responseData instanceof Arrayable) {
            $this->setResponseData($this->responseData->toArray());
        } elseif (!is_array($this->responseData) && !is_string($this->responseData)) {
            $this->setResponseData("Invalid Data");
        }

        return $this->responseData;
    }

    /**
     * @param mixed $responseData
     *
     * @return MbAudit
     */
    public function setResponseData($responseData)
    {
        $this->responseData = $responseData;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
     * @param array[] $responseHeader
     *
     * @return MbAudit
     */
    public function setResponseHeader($responseHeader)
    {
        $this->responseHeader = $responseHeader;

        return $this;
    }

    /**
     * @return int
     */
    public function getResponseStatusCode()
    {
        return $this->responseStatusCode;
    }

    /**
     * @param int $responseStatusCode
     *
     * @return MbAudit
     */
    public function setResponseStatusCode($responseStatusCode)
    {
        $this->responseStatusCode = $responseStatusCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSessionData()
    {
        return $this->request->hasSession() ? $this->request->session()->all() : null;
    }

    /**
     * @return array[]
     */
    public function getMbQueryLog()
    {
        return MbDatabase::getQueryLog();
    }

    /**
     * @return array[]
     */
    public function getWordpressQueryLog()
    {
        global $wpdb;

        return $wpdb->queries;
    }

    /**
     * @return bool
     */
    public function isOnlyMbPage()
    {
        return $this->onlyMbPage;
    }

    /**
     * @param bool $onlyMbPage
     *
     * @return MbAudit
     */
    public function setOnlyMbPage($onlyMbPage)
    {
        $this->onlyMbPage = $onlyMbPage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStoreView()
    {
        return $this->storeView;
    }

    /**
     * @param bool $storeView
     *
     * @return MbAudit
     */
    public function setStoreView($storeView)
    {
        $this->storeView = $storeView;

        return $this;
    }

    /**
     * @return MocaBonita
     */
    public function getMocaBonita()
    {
        return $this->mocaBonita;
    }

    /**
     * @param MocaBonita $mocaBonita
     *
     * @return MbAudit
     */
    public function setMocaBonita($mocaBonita)
    {
        $this->mocaBonita = $mocaBonita;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'time'             => time(),
            'user'             => $this->getUser(),
            'request'          => $this->getRequest(),
            'response'         => [
                'type'   => $this->getResponseType(),
                'data'   => $this->getResponseData(),
                'header' => $this->getResponseHeader(),
                'status' => $this->getResponseStatusCode(),
            ],
            'mocabonita_query' => $this->getMbQueryLog(),
            'wordpress_query'  => $this->getWordpressQueryLog(),
            'session_data'     => $this->getSessionData(),
        ];
    }

    /**
     * @return array|callable
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param array|callable $storage
     *
     * @return MbAudit
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Run MbAudit
     */
    final public function run()
    {
        if (!$this->request->isMocaBonitaPage() && !$this->request->isShortcode() && $this->isOnlyMbPage()) {
            return true;
        }

        if (is_array($this->getStorage()) || is_callable($this->getStorage())) {
            return call_user_func_array($this->getStorage(), [$this->toArray()]);
        }

        return false;
    }

    /**
     * MbAudit constructor.
     */
    public function __construct()
    {
        $this->setOnlyMbPage(true);
        $this->setStoreView(false);
    }

}