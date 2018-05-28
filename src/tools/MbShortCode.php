<?php

namespace MocaBonita\tools;

use MocaBonita\MocaBonita;
use MocaBonita\view\MbView;

/**
 * Main class of the MocaBonita Shortcode
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
class MbShortCode
{

    /**
     * Stores the current MbView of the action
     *
     * @var MbView
     */
    protected $mbView;

    /**
     * Shortcode name
     *
     * @var string
     */
    private $name;

    /**
     * Shortcode MbAction
     *
     * @var MbAction
     */
    private $shortcodeAction;

    /**
     * Shortcode post MbAction
     *
     * @var MbAction
     */
    private $shortcodePostAction;

    /**
     * Shortcode MbAsset
     *
     * @var MbAsset
     */
    private $mbAsset;

    /**
     * Shortcode Parameters
     *
     * @var array
     */
    private $parameters;

    /**
     * Data  Parameters
     *
     * @var mixed
     */
    private $postData;

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return MbShortCode
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get MbAction
     *
     *
     * @return MbAction
     */
    public function shortcodeAction()
    {
        return $this->shortcodeAction;
    }

    /**
     * Set MbAction
     *
     * @param MbAction $shortcodeAction
     *
     * @return MbShortCode
     */
    public function setAction(MbAction $shortcodeAction)
    {
        $this->shortcodeAction = $shortcodeAction;

        return $this;
    }

    /**
     * @return MbAction
     */
    public function shortcodePostAction()
    {
        return $this->shortcodePostAction;
    }

    /**
     * @param string|callable|mixed $action
     *
     * @return MbShortCode
     *
     * @throws MBException
     */
    public function addPostAction($action)
    {
        $mbAction = new MbAction($this->shortcodeAction()->getMbPage(), $this->getName());

        $mbAction->setShortcode(true)->actionResolver($action);

        $this->shortcodePostAction = $mbAction;

        return $this;
    }

    /**
     * Get MbAsset
     *
     * @return MbAsset
     */
    public function getMbAsset()
    {
        return $this->mbAsset;
    }

    /**
     * Set MbAsset
     *
     * @param MbAsset $mbAsset
     *
     * @return MbShortCode
     */
    public function setMbAsset(MbAsset $mbAsset)
    {
        $this->mbAsset = $mbAsset;

        return $this;
    }

    /**
     * getParameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * setParameters
     *
     * @param array $parameters
     *
     * @return MbShortCode
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return MbView
     */
    public function getMbView()
    {
        return $this->mbView;
    }

    /**
     * @param MbView $mbView
     *
     * @return MbShortCode
     */
    public function setMbView(MbView $mbView)
    {
        $this->mbView = $mbView;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @param mixed $postData
     *
     * @return MbShortCode
     */
    public function setPostData($postData)
    {
        $this->postData = $postData;

        return $this;
    }

    /**
     * Shortcode construct
     *
     * @param string                $name nome do shortcode
     * @param MbPage                $mbPage
     * @param string|callable|mixed $action
     */
    public function __construct($name, MbPage $mbPage, $action)
    {

        $mbAction = new MbAction($mbPage, $name);

        $mbAction->setShortcode(true)
            ->setFunctionComplement('Shortcode')
            ->actionResolver($action);

        $this->setName($name)
            ->setAction($mbAction)
            ->setMbAsset(new MbAsset());
    }

    /**
     * Run shortcode
     *
     * @param MbAsset    $mbAsset
     *
     * @param MbRequest  $mbRequest
     *
     * @param MbResponse $mbResponse
     */
    public function runShortcode(MbAsset $mbAsset, MbRequest $mbRequest, MbResponse $mbResponse)
    {
        $shortCode = $this;

        //Initialize Shorcode
        add_shortcode($this->getName(), function ($attributes, $content, $tags) use ($shortCode, $mbAsset, $mbRequest, $mbResponse) {

            $mocaBonita = MocaBonita::getInstance();

            try {

                if(is_null($shortCode->getMbView())){
                    $shortCode->setMbView(new MbView());
                }

                $shortCode->setParameters([
                    'attributes' => $attributes,
                    'content'    => $content,
                    'tags'       => $tags,
                ]);

                $mbRequest->setMbPage($shortCode->shortcodeAction()->getMbPage());
                $mbRequest->setMbAction($shortCode->shortcodeAction());

                $mbRequest->setShortcode(true);

                $shortCode->getMbAsset()->mergeAsset($mbRequest->getMbPage()->getMbAsset(), true);
                $shortCode->getMbAsset()->mergeAsset($mbAsset, true);

                //Add shortcode assets
                $shortCode->getMbAsset()
                    ->setActionEnqueue('front')
                    ->runAssets($shortCode->getName(), true);

                MbEvent::callEvents($mocaBonita, MbEvent::BEFORE_SHORTCODE, $shortCode);

                try {

                    ob_start();

                    MbEvent::callEvents($mocaBonita, MbEvent::BEFORE_ACTION, $shortCode->shortcodeAction());

                    $shortCode->getMbView()->setView('shortcode', 'shortcode', $shortCode->shortcodeAction()->getName());

                    if($mbRequest->isMethod('post') && $shortCode->shortcodePostAction() instanceof MbAction) {
                        $actionPostResponse = $mocaBonita->runAction($shortCode->shortcodePostAction(), $shortCode->getMbView(), [
                            $mbRequest,
                            $mbResponse,
                            $shortCode,
                        ]);

                        $shortCode->setPostData($actionPostResponse);
                    }

                    $actionResponse = $mocaBonita->runAction($shortCode->shortcodeAction(), $shortCode->getMbView(), [
                        $mbRequest,
                        $mbResponse,
                        $shortCode,
                    ]);

                    MbEvent::callEvents($mocaBonita, MbEvent::AFTER_ACTION, $shortCode->shortcodeAction());

                } catch (\Exception $e) {
                    MbEvent::callEvents($mocaBonita, MbEvent::EXCEPTION_ACTION, $e);
                    $actionResponse = $e->getMessage();
                } finally {
                    MbEvent::callEvents($mocaBonita, MbEvent::FINISH_ACTION, $shortCode->shortcodeAction());
                    ob_end_clean();
                }

                if (is_null($actionResponse)) {
                    $actionResponse = $shortCode->shortcodeAction()->getMbPage()->getController()->getMbView();
                }

                MbEvent::callEvents($mocaBonita, MbEvent::AFTER_SHORTCODE, $shortCode);

            } catch (\Exception $e) {
                $actionResponse = $e->getMessage();
            } finally {
                $mbResponse->setContent($actionResponse);
                $mocaBonita->getMbAudit()->setResponseType('shortcode');
                $mbResponse->sendContent();
            }
        });
    }

    /**
     * Get MbAction
     *
     * @deprecated use MbShortCode::shortcodeAction()
     *
     * @return MbAction
     */
    public function getMbAction()
    {
        return $this->shortcodeAction;
    }

    /**
     * Set MbAction
     *
     * @param MbAction $mbAction
     *
     * @deprecated use MbShortCode::setAction($mbAction)
     *
     * @return MbShortCode
     */
    public function setMbAction(MbAction $mbAction)
    {
        $this->shortcodeAction = $mbAction;

        return $this;
    }

}