#MocaBonita - Wordpress
O MocaBonita é um framework desenvolvido para auxiliar na criação de plugins wordpress. 

Vantagens:
- Padrão MVC
- Composer
- Validações
- ORM
- Orientação a Objeto
- Templates e Views
- Eventos
- Seus recusos são carregados por completo apenas quando necessário
- Não interfere o ciclo de vida do wordpress, desde que não exista um evento para isto
- Fácilidade aprendizado

```
Antes de começar, é recomendado que você faça uma leitura desse artigo:
https://codex.wordpress.org/pt-br:Escrevendo_um_Plugin#Nomes.2C_arquivos_e_Locais
```

[Documentação PHP](https://jhorlima.github.io/wp-mocabonita/)

[1º Criando o plugin](#1º-criando-o-plugin)

[2º Importar o MocaBonita](#2º-importar-o-mocabonita)

[3º Configurar o plugin](#3º-configurar-o-plugin)

[4º Configuração das Páginas](#4º-configuração-das-páginas)


####1º Criando o plugin ####
Acesse a pasta `wp-content/plugins` dentro da pasta onde o **wordpress** está instalado, depois crie uma nova pasta com o nome do seu plugin, Ex: `exemplo-plugin`.

####2º Importar o MocaBonita ####
Em primeiro lugar é necessário ter o composer instalado no computador. 

Depois de instalado, execute o código abaixo pelo terminal na pasta do seu plugin.

```sh
$ composer require jhorlima/wp-mocabonita:dev-master --update-no-dev
``` 

####3º Configurar o plugin ####
Depois da instalação do MocaBonita e suas dependencias do composer, crie um arquivo chamado `index.php` dentro da pasta do plugin e depois adicione o seguinte código nele:

```php
<?php
/**
 * Plugin Name: Exemplo de Plugin
 * Plugin URI: http://exemplo.plugin.com
 * Description: Um exemplo de Plugin WordPress com MocaBonita
 * Version: 1.0.0
 * Author: Fulando
 * Author URI: http://www.github.com/fulando
 * License: GLPU
 * 
 * @doc: https://developer.wordpress.org/plugins/the-basics/header-requirements/
*/

/**
 * Namespace base do Plugin
 * @doc: http://php.net/manual/pt_BR/language.namespaces.php
*/
namespace ExemploPlugin;

use MocaBonita\MocaBonita;
use MocaBonita\tools\MbPage;
use MocaBonita\tools\MbPath;
use ExemploPlugin\controller\ExemploController;

/**
 * Impedir que o plugin seja carregado fora do Wordpress
 * @doc: https://codex.wordpress.org/pt-br:Escrevendo_um_Plugin#Arquivos_de_Plugin
*/
if (!defined('ABSPATH')) {
    die('Acesso negado!' . PHP_EOL);
}

/**
 * Carregar o autoload do composer
 * Adicionar o namespace atual para ser interpretado pelo autoload do composer
*/
$pluginPath = plugin_dir_path(__FILE__);
$loader = require $pluginPath . "vendor/autoload.php";
$loader->addPsr4(__NAMESPACE__ . '\\', $pluginPath);

/**
 * Callback que será chamado ao ativar o plugin (Opicional)
 * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_active
*/
MocaBonita::active(function (MocaBonita $mocabonita){
    //
});

/**
 * Callback que será chamado ao desativar o plugin (Opicional)
 * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_deactive
*/
MocaBonita::deactive(function (MocaBonita $mocabonita){
    //
});

/**
 * Callback que terão as configurações do plugin
 * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_plugin
*/
MocaBonita::plugin(function (MocaBonita $mocabonita){
    
    /**
    * Criando uma página para o Plugin
    */    
    $paginaExemplo = MbPage::create('Exemplo');
    
     /**
     * Aqui podemos configurar alguns ajustes da página
     * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.tools.MbPage.html
     */  
    $paginaExemplo->setMenuPosition(1)
        ->setDashicon('dashicons-admin-site')
        ->setRemovePageSubmenu();
 
    /**
    * Criando outra página para o Plugin
    * É possível inúmeras páginas ao plugin
    */ 
    $paginaOutra = MbPage::create('Outra');
 
    /**
    * Para que cada página funcione corretamente, é necessário criar uma Class que extenda de MbController 
    * e depois adiciona-la à página, através de seu nome.
    * @doc: http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class 
    */ 
    $paginaOutra->setController(ExemploController::class);
 
    /**
    * Cada método da controller pode ser representado por uma action na página, 
    * entretanto o método na Controller deve ter o sufixo "Action", Ex: cadastrarAction(MbRequest $mbRequest, MbResponse $mbResponse).
    * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.tools.MbAction.html 
    */ 
    $paginaOutra->addMbAction('cadastrar');
 
    /**
    * Por padrão, ao ser criado uma página, uma action chamada index é criada, contudo é possível ajustar 
    * suas configurações, assim como de qualquer outra action. 
    * Assim como as páginas, as actions tem suas próprias configurações. 
    */
    $paginaOutra->getMbAction('index')
                 ->setRequiresAjax(true)
                 ->setRequiresMethod('GET')
                 ->setRequiresLogin(false);
 
    $paginaOutra->addMbAction('apagar')
                 ->setRequiresMethod('DELETE');
 
    $paginaOutra->addMbAction('atualizar')
                 ->setRequiresMethod('PUT');
 
    /**
    * Cada página pode ter suas capacidades alteradas, contudo elas só terão efeitos se for necessário o login do Wordpress
    * @doc: https://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table
    */
    $paginaOutra->setCapability('read');
 
    /**
    * Caso seu plugin precise de um shortcode, você pdoe adiciona-lo associando à página.
    * Seu comportamento é semelhante a de uma action, contudo seu sufixo deve ser "Shortcode", Ex: exemploShortcode(array $attributes, $content, $tags).
    * @doc: https://codex.wordpress.org/Shortcode_API
    * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_addMbShortcode
    */
    $mocabonita->addMbShortcode('exemplo_shortcode', $paginaOutra, 'exemplo');
 
    /**
    * Vamos criar uma terceira página que será uma subpágina da página Outra 
    */
    $paginaTeste = MbPage::create('Teste');
  
    /**
    * É possível tornar uma página como subpágina de outra. 
    * A única diferença entre uma página e uma subpágina é que no menu administrativo, a subpágina passa a ser um submenu
    * da página principal. Além disso, ao adicionar uma subpágina, você não precisa adiciona-la ao MocaBonita, 
    * como vamos fazer nas próximas linhas com as outras duas páginas.
    * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.tools.MbPage.html#method_setSubPage
    */
    $paginaOutra->setSubPage($paginaTeste);
    
    /**
     * Após finalizar todas as configurações da página, podemos adiciona-las ao MocaBonita para que elas possam ser 
     * usadas pelo Wordpress. Caso uma página não seja adicionada, apenas os shortcodes relacionados a ela serão 
     * executados.
     */
    $mocabonita->addMbPage($paginaExemplo);
    $mocabonita->addMbPage($paginaOutra);
 
    /**
    * É possível também definir assets ao plugin, wordpress ou página, basta obter seu MbAsset.
    * Nos assets é possível adicionar css e javascript ao Wordpress.
    * A class MbPath também pode ser utilizada para auxiliar nos diretórios do wordpress.
    * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.tools.MbAsset.html
    * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.tools.MbPath.html
    * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_getAssetsPlugin
    * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_getAssetsWordpress
    * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.tools.MbPage.html#method_getMbAsset
    */
    $mocabonita->getAssetsPlugin()
               ->setCss(MbPath::pBwDir('bootstrap/dist/css/bootstrap.min.css'))
               ->setCss(MbPath::pCssDir('app.css'));
    
}, true);
//O ultimo parâmetro de MocaBonita::plugin é opcional e define se o plugin está em desenvolvimento.
```

Lembre-se de editar as anotações do começo da página para o reconhecimento do plugin. 
Recomendamos que o namespace do plugin seja semelhante ao nome da pasta em **`UpperCamelCase`**.

Recomendamos que sua estrutura interna das páginas sejam assim:

`controller` : Nesta pasta ficarão as controllers do plugin.

`model` : Nesta pasta ficarão as models do plugin.

`view` : Nesta pasta ficarão as views e templates do plugin. 

`event` : Nesta pasta ficarão os eventos do plugin.

`public` : Nesta pasta ficarão os arquivos de images, css e javascript do plugin. 

Crie também as pastas `images`, `css` e `js` dentro da pasta `public`, elas poderão ser obtidas através do MbPath


*Lembre-se que nas pastas `controller`, `model` e `service` você precisará definir os namespaces nas classes php.


####4º Páginas ####

Em construção
