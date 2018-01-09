# Plugin módulo de atividade "mod_uploadfile" Moodle

Este plugin foi desenvolvido com o intuito de ilustrar o uso de algumas api's moodle que proporcionam o upload e exibição de um arquivo no moodle.

**Principais api's utilizadas**

  - [Moodleform][moodleform] - Api para criação de formulários
  - [MoodleFile][moodlefile] - Api para criação de formulários

### Installation

Este plugin não acrescenta nenhuma funcionalidade no moodle, ele foi criado apenas para ilustrar aos desenvolvedores moodle como é o processo de postagem e recuperação de um arquivo no moodle. Portanto caso não seja um desenvolvedor, creio que não lhe será util.

## Resultados e discussões

**Arquivo: upload.php**

Este arquivo é o responsável pela criação e gerência do formulário utilizado para este estudo.

en - Create some options for the file manager
pt_br -  Definindo algumas opções para o "filemanager" você pode definir quais tipos de arquivos deseja permitir para upload e também o tamanho máximo. [leia mais sobre os tipos de arquivos permitidos aqui] [listadearquivos] 


```php
$filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 50, 'context' => $context);
$customdata = array('filemanageropts' => $filemanageropts);
```


   [moodleform]: <https://docs.moodle.org/dev/Form_API>
   [moodlefile]: <https://docs.moodle.org/dev/File_API>
   [listadearquivos]: <https://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms>
   
   
   
