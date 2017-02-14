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

[1º Criando o plugin](#criar-plugin)

[2º Importar o MocaBonita](#importar-mocabonita)

[3º Configurar o plugin](#configurar-mocabonita)


####1º Criando o plugin ####
Acesse a pasta `wp-content/plugins` dentro da pasta onde o **wordpress** está instalado, depois crie uma nova pasta com o nome do seu plugin, Ex: `plugin-teste`.

####2º Importar o MocaBonita ####
Em primeiro lugar é necessário ter o composer instalado no computador. 

Depois de instalado, crie um arquivo `composer.json` na pasta do plugin e depois adicione o seguinte conteudo nele  

```json
{
    "require": {
        "Jhorzyto/wp-mocabonita": "*"
    }
}
```

Após isso, agora execute `$ composer.phar install –no-dev`.

####3º Configurar o plugin ####
Depois de instalado as dependencias do composer corretamente, crie o arquivo `index.php` na pasta do plugin e adicione o seguinte conteudo nele:

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

namespace ExemploPlugin;

use MocaBonita\MocaBonita;

$loader = require "./vendor/autoload.php";
$loader->addPsr4(__NAMESPACE__ . '\\', './');

MocaBonita::loader(function (MocaBonita $mocabonita){
    
    /**
    * Aqui será adicionado as configurações do MocaBonita 
    * 
    * abaixo vamos preencher esta área com as configurações   
    * 
    */
    
});
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

As páginas do MoçaBonita são