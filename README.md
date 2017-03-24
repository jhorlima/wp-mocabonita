#MocaBonita - Wordpress
O MocaBonita é um framework desenvolvido para auxiliar na criação de plugins wordpress. 

Vantagens:
- Padrão MVC
- Rest API
- Composer
- Classes de apoio a validação e banco de dados
- POST, PUT e DELETE request com JSON no Raw
- Orientação a Objeto
- Templates e Views
- Serviços (Eventos que ocorrem antes de executar a controller)
- Seus recusos são carregados por completo apenas quando necessário
- Não interfere o ciclo de vida do wordpress, desde que não exista um serviço para isto
- Fácil curva de aprendizado
- Outros..

[Documentação PHP](https://jhorlima.github.io/wp-mocabonita/)

[1º Criando o plugin](#1º-criando-o-plugin)

[2º Importar o MocaBonita](#2º-importar-o-mocabonita)

[3º Configurar o plugin](#3º-configurar-o-plugin)

[4º Configuração das Páginas](#4º-configuração-das-páginas)


####1º Criando o plugin ####
Acesse a pasta `wp-content/plugins` dentro da pasta onde o **wordpress** está instalado, depois crie uma nova pasta com o nome do seu plugin, Ex: `plugin-teste`.

####2º Importar o MocaBonita ####
Em primeiro lugar é necessário ter o composer instalado no computador. 

Depois de instalado, execute 

```sh
$ composer require jhorlima/wp-mocabonita:dev-master --update-no-dev
``` 

pelo terminal na pasta do seu plugin do wordpress.

####3º Configurar o plugin ####
Depois de instalado as dependencias do composer corretamente, 
crie o arquivo `index.php` na pasta do plugin e adicione o seguinte conteudo nele:

```php
<?php
/*
    Plugin Name: Nome do Plugin
    Plugin URI: Link do Plugin
    Description: Descrição do Plugin
    Version: Versão do Plugin
    Author: Autor do Plugin
    Author URI: Link do Autor do Plugin
    License: Licença do Plugin
*/

//Use das classes usadas
namespace ExemploPlugin;

use MocaBonita\MocaBonita;

//Carregar o autoload do composer
$pluginPath = plugin_dir_path(__FILE__);
$loader = require $pluginPath . "vendor/autoload.php";
$loader->addPsr4(__NAMESPACE__ . '\\', $pluginPath);

MocaBonita::loader(function (MocaBonita $mocabonita){
    
    /**
    * Aqui será adicionado as configurações do MocaBonita 
    * 
    * abaixo vamos preencher esta área com as configurações   
    * 
    */
    
}, true);
//O ultimo parâmetro de MocaBonita::loader é opcional e define se o plugin está em desenvolvimento.
```

Lembre-se de editar as anotações para o reconhecimento do plugin por conta do wordpres. Recomendamos que o namespace do plugin seja semelhante ao nome da pasta, mas em **`UpperCamelCase`**.

Antes de começar a configurar o MocaBonita, vamos criar as pastas do MVC e outras. Dentro da pasta do plugin, crie as seguintes páginas

`controller` : Nesta pasta ficarão as controllers do plugin.

`model` : Nesta pasta ficarão as models do plugin.

`view` : Nesta pasta ficarão as views e templates do plugin. 

`service` : Nesta pasta ficarão os services do plugin (Falaremos dele abaixo).

`public` : Nesta pasta ficarão os arquivos de images, css e javascript do plugin. 

Crie também as pastas `images`, `css` e `js` dentro da pasta `public`


*Lembre-se que nas pastas `controller`, `model` e `service` você precisará definir os namespaces nas classes php.


####4º Configuração das Páginas ####

As páginas do MoçaBonita é uma espécie de container cheio de ações que serão executadas pelo wordpress. 
Cada página precisa ter um `nome`, `capacity` do wordpress, `slug` (link da página no wordpress), `icone do menu`, 
`posição no menu`, sua `controller` (Instancia de MocaBonita\controller\Controller), `assets` e suas `ações`, 
por padrão já vem a indexAction.

O MocaBonita precisa de no mínimo uma página para funcionar corretamente. 

Para criar uma página no MocaBonita, crie uma instância de `MocaBonita\tools\Paginas` e depois adicione ao MocaBonita.
Veja o exemplo abaixo:

```php
use MocaBonita\tools\Paginas;

MocaBonita::loader(function (MocaBonita $mocabonita){
    
    $exemploPagina = new Paginas();
    
    $mocabonita->adicionarPagina($exemploPagina);
    
}, true);
```

Com isso, você já vera o resultado ao acessar o painel adminstrativo do wordpress `wp-admin/admin.php` 
e clicar no menu `Moça Bonita`.

Por padrão, a página já vem com essas definições mas podem ser editáveis:

```php
$exemploPagina
    ->setNome("Moça Bonita")            //Nome da Página
    ->setCapacidade("manage_options")   //Capacity do WP quando acessar pelo painel administrativo (https://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table)
    ->setSlug("moca_bonita")            //Slug da Página (wp-admin/admin.php?page=moca_bonita)
    ->setIcone("dashicons-editor-code") //Icone do Menu (https://developer.wordpress.org/resource/dashicons/)
    ->setEsconderMenu(false)            //Esconder menu no WordPress
    ->setAssets(new Assets())           //Assets que aparecerão quando acessar a página de alguma forma (CSS e JS)
    ->setPaginaParente(null)            //Página pai, caso esta seja uma subpágina
    ->setMenuPrincipal(true)            //Definir página como menu principal no wordpress 
    ->setSubmenu(false)                 //Definir página como submenu no wordpress, necessário uma página parente
    ->setPosicao(100)                   //Posição da página no menu do wordpress
    ->adicionarAcao('index');           //Adicionar a indexAction para a página
```

Por padrão, a Ação index já vem com essas definições mas podem ser editáveis:

```php
$acaoIndex = $exemploPagina->getAcao('index'); //Obter MocaBonita\tools\Acoes('index') da página

$acaoIndex
    ->setNome('index')         //Nome da ação que é enviado pela rota (wp-admin/admin.php?page=moca_bonita&action=index)
    ->isLogin(true)            //Definir se a Ação precisa acessar com usuário conectado ao wordpress
    ->setAjax(false)           //Definir se a Ação precisa acessar pelo admin-ajax.php 
    ->setRequisicao(null)      //Definir um método de requisição exclusivo para a ação, ex: POST, DELETE, PUT, GET. Caso for null, aceitará todos
    ->setMetodo('index')       //Nome do método do Controller da página sem o complemento Action
    ->setShortcode(false)      //Definir se a Ação é um shortcode do wordpress
    ->setComplemento('Action') //Complemento do método, por padrão esta "Action" para diferenciar os métodos que são ações nas controllers 
    ->setCapacidade(null);     //Caso o islogin seja true, a capacidade do usuário logado é precisa atender a capacidade definida, caso a capacidade seja null, a capacidade da página é comparada.
```

A página pode ter diversas ações, cada ação com seu próprio link exclusivo, além de definir método de requisição, 
capacidade exclusiva e validação de login se necessário.

Para que a página funcione corretamente, é necessário ter um Controller, então crie uma Classe para Controller na pasta controller e 
extenda a Controller do MocaBonita. Veja abaixo:


```php
<?php

namespace ExemploPlugin\controller;

use MocaBonita\controller\Controller;

class Exemplo extends Controller {
    
    /**
     * Ação da controller
     *
     * Se a requisição for para admin.php ou admin-post.php 
     * Se o retorno for null, ele irá chamar a view desta controller e redenrizar
     * Se o retorno for string, ele irá imprimir a string na tela
     * Se o retorno for View, ele irá redenrizar a view desta controller
     * Se o retorno for qualquer outro tipo, ele irá fazer um var_dump do retorno
     * Se existir uma exception, retonará a mensagem de erro do exception 
     * 
     * Se a requisição for para admin-ajax.php 
     * Se o retorno for string, ele retornará um JSON com chave "content" contendo a string
     * Se o retorno for Array, ele retornará um JSON desse array
     * Se o retorno for qualquer outro tipo, ele retornará uma requisição de erro, informando "Nenhum dado foi retornado!"
     * Se existir uma exception, ele retornará uma requisição de erro, informando o erro do exception 
     * 
     * @return null|string|View|array[]|void
     */
    public function indexAction()
    {
        return $this->view;
    }

}
```

Por padrão, a Controller já vem com alguns métodos definidos para obter o slug da página atual, o nome da ação, o 
tipo de requisição, se a requisição é ajax, se for shortcode, a query http get e os dados enviados pela requisição 
como `$_POST` ou `JSON`. O nome da ação definida na página é o mesmo nome do método, contudo pode ser alterado pelo método 
`setMetodo($metodo)`, além do complemento Action que pode ser substituido por `setComplemento()`.

