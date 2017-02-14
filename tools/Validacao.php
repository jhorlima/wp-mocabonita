<?php
namespace MocaBonita\tools;

use \Exception;

/**
 * Classe de Validação do Moça Bonita.
 *
 * Documentação pendente....
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\Tools
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class Validacao
{

    /**
     * Regras adicionadas para validação
     *
     * @var \Closure[]
     */
    private static $regras = [];

    /**
     * Mensagens de erros da validação
     *
     * @var array[]
     */
    private static $mensagens = [];

    /**
     * O método mágico __clone() é declarado como private para impedir a clonagem de uma instância da classe através
     * do operador clone.
     *
     */
    final private function __clone()
    {
        //
    }

    /**
     * O método mágico __wakeup() é declarado como private para evitar unserializing de uma instância da classe via
     * a função global unserialize ().
     *
     */
    final private function __wakeup()
    {
        //
    }

    /**
     * O construtor __construct() é declarado como private para evitar a criação de uma nova instância fora da classe
     * através do operador new.
     *
     */
    final protected function __construct()
    {
        if (method_exists($this, 'inicializar')) {
            $this->inicializar();
        }
    }

    /**
     * @param string|null $atributo
     * @return array|\array[]
     */
    public static function getMensagens($atributo = null)
    {
        return is_null($atributo) ? self::$mensagens : self::$mensagens[$atributo];
    }

    /**
     * Atribuir todas as mensagens
     *
     * @param array $mensagens
     */
    private static function setMensagens(array $mensagens)
    {
        self::$mensagens = $mensagens;
    }

    /**
     * Atribuir mensagem individual para um atributo
     *
     * @param string $atributo
     * @param string $mensagem
     */
    private static function setMensagem($atributo, $mensagem)
    {
        if (!isset(self::$mensagens[$atributo]))
            self::$mensagens[$atributo] = [];

        self::$mensagens[$atributo][] = $mensagem;
    }

    /**
     * Realizar a verificação dos dados de acordo com as regras definidas no atributo regras
     *
     *
     * @param array $dados conjuto de dados para ser validados
     * @param array $regras conjuto de regras para validar os dados
     * @param bool $tratarRetorno tratar o retorno do metodo para remover atributos não validados do retorno
     * @param bool $permitirNulos permitir atributos nulos ou não existente nos dados definidos das regras
     * @return array|bool
     */
    public static function verificar(array $dados, array $regras, $tratarRetorno = false, $permitirNulos = false)
    {
        //Obter atributos das regras
        $atributos = array_keys($regras);

        //Limpar mensagens anteriores
        self::setMensagens([
            //Array vazio
        ]);

        //Carregar regras padrões
        self::carregarRegras();

        foreach ($atributos as &$atributo) {

            $existeAtributo = array_key_exists($atributo, $dados);
            $atributoNulo = $existeAtributo ? is_null($dados[$atributo]) : true;

            if ((!$existeAtributo && !$permitirNulos) || ($atributoNulo && !$permitirNulos)) {

                self::setMensagem($atributo, "O atributo '{$atributo}' não foi encontrado ou é nulo!");

            } elseif (!$existeAtributo && $permitirNulos) {
                $dados[$atributo] = !$existeAtributo ? null : $dados[$atributo];
                $regrasAtributo = explode("|", $regras[$atributo]);
                foreach ($regrasAtributo as $regraAtributo) {
                    self::processarRegra($regraAtributo, $dados[$atributo], $atributo);
                }

            } else {
                $regrasAtributo = explode("|", $regras[$atributo]);
                foreach ($regrasAtributo as $regraAtributo) {
                    self::processarRegra($regraAtributo, $dados[$atributo], $atributo);
                }
            }

        }

        if ($tratarRetorno) {
            $atributosEnviados = array_keys($dados);
            foreach ($atributosEnviados as &$atributo) {
                if (!in_array($atributo, $atributos)) {
                    unset($dados[$atributo]);
                }
            }
        }

        return empty(self::$mensagens) ? $dados : false;
    }

    /**
     * Carregar as regras padrões do MocaBonita
     *
     * @return null
     */
    private static function carregarRegras()
    {
        if(!empty(self::$regras))
            return null;

        /**
         * Validação de String e contagem de seus caracteres podendo ser definido como parametros[].
         *
         * Primeiro elemento do parametro é a quantidade minima de caracteres(opcional)
         * Segundo elemento do parametro é a quantidade maxima de caracteres(opcional)
         *
         */
        self::adicionarRegra('string', function ($valor, $atributo, array $parametros) {
            $isString = is_string($valor);
            $isNull = is_null($valor);

            if (!$isNull) {

                if (!$isString) {
                    throw new Exception("O atributo '{$atributo}' não é um string!");
                }

                $menorQntCaracters = array_shift($parametros);
                $maiorQntCaracters = array_shift($parametros);
                $qntCaracteres = strlen($valor);

                if (!is_null($menorQntCaracters) && is_numeric($menorQntCaracters)) {
                    $menorQntCaracters = intval($menorQntCaracters);
                } else {
                    $menorQntCaracters = false;
                }

                if (!is_null($maiorQntCaracters) && is_numeric($maiorQntCaracters)) {
                    $maiorQntCaracters = intval($maiorQntCaracters);
                } else {
                    $maiorQntCaracters = false;
                }

                if ($menorQntCaracters) {

                    if ($qntCaracteres < $menorQntCaracters) {
                        throw new Exception(
                            "O atributo '{$atributo}' deve ter no minimo '{$menorQntCaracters}' caractere(s)!"
                        );
                    } elseif ($maiorQntCaracters && $qntCaracteres > $maiorQntCaracters) {
                        throw new Exception(
                            "O atributo '{$atributo}' deve ter no maximo '{$maiorQntCaracters}' caracteres!"
                        );
                    }

                }
            }

            return $valor;

        });

        /**
         * Validação de String, filtro Trim e contagem de seus caracteres podendo ser definido como parametros[].
         *
         * Primeiro elemento do parametro é a quantidade minima de caracteres(opcional)
         * Segundo elemento do parametro é a quantidade maxima de caracteres(opcional)
         *
         */
        self::adicionarRegra('string_t', function ($valor, $atributo, array $parametros) {
            $isString = is_string($valor);
            $isNull = is_null($valor);

            if (!$isNull) {

                if (!$isString) {
                    throw new Exception("O atributo '{$atributo}' não é um string!");
                } else {
                    $valor = trim($valor);
                }

                $menorQntCaracteres = array_shift($parametros);
                $maiorQntCaracteres = array_shift($parametros);
                $qntCaracteres = strlen($valor);

                if (!is_null($menorQntCaracteres) && is_numeric($menorQntCaracteres)) {
                    $menorQntCaracteres = intval($menorQntCaracteres);
                } else {
                    $menorQntCaracteres = false;
                }

                if (!is_null($maiorQntCaracteres) && is_numeric($maiorQntCaracteres)) {
                    $maiorQntCaracteres = intval($maiorQntCaracteres);
                } else {
                    $maiorQntCaracteres = false;
                }

                if ($menorQntCaracteres) {

                    if ($qntCaracteres < $menorQntCaracteres) {
                        throw new Exception(
                            "O atributo '{$atributo}' deve ter no minimo '{$menorQntCaracteres}' caractere(s)!"
                        );
                    } elseif ($maiorQntCaracteres && $qntCaracteres > $maiorQntCaracteres) {
                        throw new Exception(
                            "O atributo '{$atributo}' deve ter no maximo '{$maiorQntCaracteres}' caracteres!"
                        );
                    }

                }
            }

            return $valor;

        });

        /**
         * Validação de Números, valor minimo e maximo podendo ser definido como parametros[].
         *
         * Primeiro elemento do parametro é o valor minimo(opcional)
         * Segundo elemento do parametro é o valor maximo(opcional)
         *
         */
        self::adicionarRegra('numeric', function ($valor, $atributo, array $parametros) {
            $isNumeric = is_numeric($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isNumeric) {
                    throw new Exception("O atributo '{$atributo}' não é um número!");
                }

                $menorValor = array_shift($parametros);
                $maiorValor = array_shift($parametros);

                $valor = $valor + 0;

                if (!is_null($menorValor) && is_numeric($menorValor)) {
                    $menorValor = $menorValor + 0;
                } else {
                    $menorValor = false;
                }

                if (!is_null($maiorValor) && is_numeric($maiorValor)) {
                    $maiorValor = $maiorValor + 0;
                } else {
                    $maiorValor = false;
                }

                if ($menorValor) {

                    if ($valor < $menorValor) {
                        throw new Exception("O atributo '{$atributo}' deve ser maior ou igual a '{$menorValor}'!");
                    } elseif ($maiorValor && $valor > $maiorValor) {
                        throw new Exception("O atributo '{$atributo}' deve ser menor ou igual a '{$maiorValor}'!");
                    }
                }
            }

            return $valor;

        });

        /**
         * Validação de Números Inteiros, valor minimo e maximo podendo ser definido como parametros[].
         *
         * Primeiro elemento do parametro é o valor minimo(opcional)
         * Segundo elemento do parametro é o valor maximo(opcional)
         *
         */
        self::adicionarRegra('numeric_int', function ($valor, $atributo, array $parametros) {
            $isInt = is_numeric($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isInt) {
                    throw new Exception("O atributo '{$atributo}' não é um número!");
                }

                $menorValor = array_shift($parametros);
                $maiorValor = array_shift($parametros);

                $valor = intval($valor);

                if (!is_null($menorValor) && is_numeric($menorValor)) {
                    $menorValor = intval($menorValor);
                } else {
                    $menorValor = false;
                }

                if (!is_null($maiorValor) && is_numeric($maiorValor)) {
                    $maiorValor = intval($maiorValor);
                } else {
                    $maiorValor = false;
                }

                if ($menorValor) {

                    if ($valor < $menorValor) {
                        throw new Exception("O atributo '{$atributo}' deve ser maior ou igual a '{$menorValor}'!");
                    } elseif ($maiorValor && $valor > $maiorValor) {
                        throw new Exception("O atributo '{$atributo}' deve ser menor ou igual a '{$maiorValor}'!");
                    }
                }
            }

            return $valor;

        });

        /**
         * Validação de Números Double, valor minimo e maximo podendo ser definido como parametros[].
         *
         * Primeiro elemento do parametro é o valor minimo(opcional)
         * Segundo elemento do parametro é o valor maximo(opcional)
         *
         */
        self::adicionarRegra('numeric_double', function ($valor, $atributo, array $parametros) {
            $isDouble = is_numeric($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isDouble) {
                    throw new Exception("O atributo '{$atributo}' não é um número!");
                }

                $menorValor = array_shift($parametros);
                $maiorValor = array_shift($parametros);

                $valor = doubleval($valor);

                if (!is_null($menorValor) && is_numeric($menorValor)) {
                    $menorValor = doubleval($menorValor);
                } else {
                    $menorValor = false;
                }

                if (!is_null($maiorValor) && is_numeric($maiorValor)) {
                    $maiorValor = doubleval($maiorValor);
                } else {
                    $maiorValor = false;
                }

                if ($menorValor) {

                    if ($valor < $menorValor) {
                        throw new Exception("O atributo '{$atributo}' deve ser maior ou igual a '{$menorValor}'!");
                    } elseif ($maiorValor && $valor > $maiorValor) {
                        throw new Exception("O atributo '{$atributo}' deve ser menor ou igual a '{$maiorValor}'!");
                    }
                }
            }

            return $valor;

        });

        /**
         * Validação de Array, valor minimo e maximo de elementos podendo ser definido como parametros[].
         *
         * Primeiro elemento do parametro é a quantidade minima de elemento(opcional)
         * Segundo elemento do parametro é a quantidade maxima de elemento(opcional)
         *
         */
        self::adicionarRegra('array', function ($valor, $atributo, array $parametros) {
            $isArray = is_array($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isArray) {
                    throw new Exception("O atributo '{$atributo}' não é um array!");
                }

                $menorQntElem = array_shift($parametros);
                $maiorQntElem = array_shift($parametros);

                $qntElem = count($valor);

                if (!is_null($menorQntElem) && is_numeric($menorQntElem)) {
                    $menorQntElem = intval($menorQntElem);
                } else {
                    $menorQntElem = false;
                }

                if (!is_null($maiorQntElem) && is_numeric($maiorQntElem)) {
                    $maiorQntElem = intval($maiorQntElem);
                } else {
                    $maiorQntElem = false;
                }

                if ($menorQntElem) {

                    if ($qntElem < $menorQntElem) {
                        throw new Exception("O atributo '{$atributo}' deve ter no minimo '{$menorQntElem}' elemento(s)!");
                    } elseif ($maiorQntElem && $qntElem > $maiorQntElem) {
                        throw new Exception("O atributo '{$atributo}' deve ter no máximo '{$maiorQntElem}' elemento(s)!");
                    }
                }
            }

            return $valor;

        });

        /**
         * Validação de Object e instanceof podendo ser definido como parametros[].
         *
         * Primeiro elemento do parametro é o valor minimo(opcional)
         *
         */
        self::adicionarRegra('object', function ($valor, $atributo, array $parametros) {
            $isObject = is_object($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isObject) {
                    throw new Exception("O atributo '{$atributo}' não é um objeto!");
                }

                $instanceOf = array_shift($parametros);

                if (!is_null($instanceOf) && is_string($instanceOf)) {
                    $instanceOf = strval($instanceOf);
                } else {
                    $instanceOf = false;
                }

                if ($instanceOf && !$valor instanceof $instanceOf) {
                    throw new Exception("O atributo '{$atributo}' não é uma instância de '{$instanceOf}'!");
                }
            }

            return $valor;

        });

        /**
         * Validação de atributos obrigatórios.
         *
         */
        self::adicionarRegra('required', function ($valor, $atributo) {
            $isNull = is_null($valor);

            if ($isNull) {
                throw new Exception("O atributo '{$atributo}' é obrigatório!");
            }

            if ((is_string($valor) && strlen($valor) == 0) || (is_array($valor) && count($valor) == 0)){
                throw new Exception("O atributo '{$atributo}' não pode está vazia!");
            }

            return $valor;

        });

        /**
         * Filtro de TAGS HTML e Tags permitidas podendo ser definido como parametros[].
         *
         * Os parametros são as tags permitidas. ex <br>:<p>:<strong> (opcional)
         *
         */
        self::adicionarRegra('striptags', function ($valor, $atributo, array $parametros) {
            $isString = is_string($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isString) {
                    throw new Exception("O atributo '{$atributo}' não é uma string!");
                } elseif(count($parametros)){
                    $valor = strip_tags($valor, implode("", $parametros));
                } else {
                    $valor = strip_tags($valor);
                }
            }

            return $valor;

        });

        /**
         * Filtro para formatar a string para minuscula.
         *
         */
        self::adicionarRegra('str_lower', function ($valor, $atributo) {
            $isString = is_string($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isString) {
                    throw new Exception("O atributo '{$atributo}' não é uma string!");
                } else {
                    $valor = strtolower($valor);
                }
            }

            return $valor;
        });

        /**
         * Filtro para formatar a string para maiuscula.
         *
         */
        self::adicionarRegra('F_str_upper', function ($valor, $atributo) {
            $isString = is_string($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isString) {
                    throw new Exception("O atributo '{$atributo}' não é uma string!");
                } else {
                    $valor = strtoupper($valor);
                }
            }

            return $valor;
        });

        /**
         * Filtro para escape no html
         *
         */
        self::adicionarRegra('html_escape', function ($valor, $atributo) {
            $isString = is_string($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isString) {
                    throw new Exception("O atributo '{$atributo}' não é uma string!");
                } else {
                    $valor = htmlspecialchars($valor);
                }
            }

            return $valor;
        });

        /**
         * Filtro para alphanumeric na string
         *
         */
        self::adicionarRegra('alpha_numeric', function ($valor, $atributo) {
            $isString = is_string($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isString) {
                    throw new Exception("O atributo '{$atributo}' não é uma string!");
                } else {
                    $valor = preg_replace("/[^a-zA-Z0-9]+/", "", $valor);
                }
            }

            return $valor;
        });

        /**
         * Validação de String email
         *
         */
        self::adicionarRegra('email', function ($valor, $atributo) {
            $isString = is_string($valor);
            $isNull = is_null($valor);

            if (!$isNull) {
                if (!$isString) {
                    throw new Exception("O atributo '{$atributo}' não é uma string!");
                } elseif(!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("O atributo '{$atributo}' não é um e-mail válido!");
                }
            }

            return $valor;
        });

        /**
         * Validação de String Data
         *
         */
        self::adicionarRegra('date', function ($valor, $atributo){
            if(strtotime($valor)){
                return $valor;
            } else{
                throw new Exception("O atributo '{$atributo}' é uma data inválida!");
            }
        });

        /**
         * Validação de String Data e retorno timestamp
         *
         */
        self::adicionarRegra('date_ts', function ($valor, $atributo){
            $date = strtotime($valor);
            if($date){
                return $date;
            } else{
                throw new Exception("O atributo '{$atributo}' é uma data inválida!");
            }
        });

        /**
         * Validação de String Data e retorno Date ou String
         *
         * Primeiro elemento do parametro é o Timezone(opcional)
         * Segundo elemento do parametro é o formato de retorno(opcional)
         */
        self::adicionarRegra('date_object', function ($valor, $atributo, array $parametros){
            $date = strtotime($valor);

            if($date){
                $date = (new \DateTime())->setTimestamp($date);

                $timezone = array_shift($parametros);
                $format   = array_shift($parametros);

                if(!is_null($timezone)){
                    $date->setTimezone(new \DateTimeZone($timezone));
                }

                if(!is_null($format)){
                    $dateFormat = $date->format($format);
                    if(!$dateFormat){
                        throw new Exception("O atributo '{$atributo}' têm formato de data inválida!");
                    }
                }

                return $date;
            } else{
                throw new Exception("O atributo '{$atributo}' é uma data inválida!");
            }
        });

        return null;
    }

    /**
     * Adicionar uma nova regra à validação do MocaBonita
     *
     * @param $nome
     * @param \Closure $callback
     * @param bool $substituir
     */
    public static function adicionarRegra($nome, \Closure $callback, $substituir = false)
    {
        $regraExiste = isset(self::$regras[$nome]);

        if (!$regraExiste || ($regraExiste && $substituir)) {
            self::$regras[$nome] = $callback;
        }
    }

    /**
     * Processar regras individualmente
     *
     * @param $regraAtributo
     * @param $valor
     * @param $atributo
     */
    private static function processarRegra($regraAtributo, &$valor, $atributo)
    {
        $parametrosRegra = explode(":", $regraAtributo);

        foreach ($parametrosRegra as &$parametro) {
            $parametro = trim($parametro);
        }

        try {

            $regra = array_shift($parametrosRegra);
            $existeRegra = isset(self::$regras[$regra]);

            if (!$existeRegra) {
                throw new Exception("A regra '{$regra}' não foi definida na Validação!");
            }

            $callback = self::$regras[$regra];
            $retorno = $callback($valor, $atributo, $parametrosRegra, $regra);

            $valor = !is_null($retorno) ? $retorno : $valor;

        } catch (Exception $e) {
            self::setMensagem($atributo, $e->getMessage());
        }
    }
}