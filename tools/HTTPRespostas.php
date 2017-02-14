<?php

namespace MocaBonita\tools;

/**
 * Gerenciamento de requisições do moça bonita
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\Tools
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class HTTPRespostas
{
    /**
     * Constante para retornar o HTTP CODE 304
     *
     * @var string
     */
    const NOT_IMPLEMENTED = 'not_implemented';

    /**
     * Constante para retornar o HTTP CODE 400
     *
     * @var string
     */
    const REQUEST_UNAVAIABLE = 'bad_request';

    /**
     * Constante para retornar o HTTP CODE 403
     *
     * @var string
     */
    const FORBIDDEN = 'forbidden';

    /**
     * Constante para retornar o HTTP CODE 409
     *
     * @var string
     */
    const CONFLICT = 'conflict';

    /**
     * Constante para retornar o HTTP CODE 500
     *
     * @var string
     */
    const INTERNAL_ERROR = 'internal_error';

    /**
     * Constante para retornar o HTTP CODE 204
     *
     * @var string
     */
    const NO_CONTENT = 'no_content';

    /**
     * Constante para retornar o HTTP CODE 304
     *
     * @var string
     */
    const NOT_MODIFIED = 'not_modified';

    /**
     * Constante para retornar o HTTP CODE 401
     *
     * @var string
     */
    const UNAUTHORIZED = 'unauthorized';

    /**
     * Constante para retornar o HTTP CODE 404
     *
     * @var string
     */
    const NOT_FOUND = 'not_found';

    /**
     * Obter a estrutura de erro de acordo com o tipo de requisição
     *
     * @param string $tipo O tipo de requisição
     * @param string $mensagem A mensagem opicional, caso enviada
     * @return array
     */
    public static function obterHttpResposta($tipo, $mensagem = null)
    {
        if ($tipo === HTTPRespostas::REQUEST_UNAVAIABLE) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '400 - BAD REQUEST' : $mensagem,
                'code' => 400));

        } elseif ($tipo === HTTPRespostas::NOT_IMPLEMENTED) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '501 - NOT IMPLEMENTED' : $mensagem,
                'code' => 501));
        } elseif ($tipo === HTTPRespostas::FORBIDDEN) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '403 - FORBIDDEN' : $mensagem,
                'code' => 403));
        } elseif ($tipo === HTTPRespostas::CONFLICT) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '409 - CONFLICT' : $mensagem,
                'code' => 409));
        } elseif ($tipo === HTTPRespostas::NO_CONTENT) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '204 - NO CONTENT' : $mensagem,
                'code' => 204));
        } elseif ($tipo === HTTPRespostas::INTERNAL_ERROR) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '500 - INTERNAL SERVER ERROR' : $mensagem,
                'code' => 500));
        } elseif ($tipo === HTTPRespostas::NOT_IMPLEMENTED) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '304 - NOT MODIFIED' : $mensagem,
                'code' => 304));
        } elseif ($tipo === HTTPRespostas::UNAUTHORIZED) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '401 - UNAUTHORIZED' : $mensagem,
                'code' => 401));
        } elseif ($tipo === HTTPRespostas::NOT_FOUND) {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '404 - NOT FOUND' : $mensagem,
                'code' => 404));
        } else {
            return array('http_method' => array('error_message' => is_null($mensagem) ? '400 - BAD REQUEST' : $mensagem,
                'code' => 400));
        }
    }
}
