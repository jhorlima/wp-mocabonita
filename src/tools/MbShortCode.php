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
    private $mbAction;

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
     * @return MbAction
     */
    public function getMbAction()
    {
        return $this->mbAction;
    }

    /**
     * Set MbAction
     *
     * @param MbAction $mbAction
     *
     * @return MbShortCode
     */
    public function setMbAction(MbAction $mbAction)
    {
        $this->mbAction = $mbAction;

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
     * Shortcode construct
     *
     * @param string   $name nome do shortcode
     * @param MbAction $mbAction
     * @param MbAsset  $mbAsset
     */
    public function __construct($name, MbAction $mbAction, MbAsset $mbAsset)
    {
        $this->setName($name)
            ->setMbAction($mbAction)
            ->setMbAsset($mbAsset);
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

            try {

                $shortCode->setParameters([
                    'attributes' => $attributes,
                    'content'    => $content,
                    'tags'       => $tags,
                ]);

                $mbRequest->setMbPage($shortCode->getMbAction()->getMbPage());
                $mbRequest->setMbAction($shortCode->getMbAction());

                $mbRequest->setShortcode(true);

                //Add plugin assets
                $mbAsset->setActionEnqueue('front')->runAssets('plugin', true);

                //Add page assets
                $mbRequest->getMbPage()
                    ->getMbAsset()
                    ->setActionEnqueue('front')
                    ->runAssets($shortCode->getName(), true);

                //Add shortcode assets
                $shortCode->getMbAsset()
                    ->setActionEnqueue('front')
                    ->runAssets($shortCode->getName(), true);

                MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::BEFORE_SHORTCODE, $shortCode);

                try {

                    ob_start();

                    MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::BEFORE_ACTION, $shortCode->getMbAction());

                    $mbView = new MbView();

                    $mbView->setMbRequest($mbRequest)
                        ->setMbResponse($mbResponse)
                        ->setView('shortcode', 'shortcode', $shortCode->getMbAction()->getName());

                    $actionResponse = MocaBonita::getInstance()->runAction($shortCode->getMbAction(), $mbView, [
                        $mbRequest,
                        $mbResponse,
                        $shortCode,
                    ]);

                    MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::AFTER_ACTION, $shortCode->getMbAction());

                } catch (\Exception $e) {
                    MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::EXCEPTION_ACTION, $e);
                    $actionResponse = $e->getMessage();
                } finally {
                    MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::FINISH_ACTION, $shortCode->getMbAction());
                    ob_end_clean();
                }

                if (is_null($actionResponse)) {
                    $actionResponse = $shortCode->getMbAction()->getMbPage()->getController()->getMbView();
                }

                MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::AFTER_SHORTCODE, $shortCode);

            } catch (\Exception $e) {
                $actionResponse = $e->getMessage();
            } finally {
                $mbResponse->setContent($actionResponse);
                $mbResponse->sendContent();
            }
        });
    }

}