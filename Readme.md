# Plugin activity module "mod_uploadfile" Moodle

This plugin was developed with the purpose of illustrating the use of some moodle api that provide the upload and display of a file in moodle.
pt_br - Este plugin foi desenvolvido com o intuito de ilustrar o uso de algumas api's moodle que proporcionam o upload e exibição de um arquivo no moodle.

**Principais api's utilizadas**

  - [Moodleform][moodleform] - Api para criação de formulários
  - [MoodleFile][moodlefile] - Api para criação de formulários

### Installation - Instalação

Este plugin não acrescenta nenhuma funcionalidade no moodle, ele foi criado apenas para ilustrar aos desenvolvedores moodle como é o processo de postagem e recuperação de um arquivo no moodle. Portanto caso não seja um desenvolvedor, creio que não lhe será util.

## Results - Resultados e discussões

**File: uploadForm.class.php**

Requer a importação da api moodleform, após isso basta criar a classe estender de moodleform e implementar o método obrigatório da classe pai que é o definition(). Tudo isso está disponível na documentação [Moodleform][moodleform]. 

```php
require($CFG->dirroot . '/course/moodleform_mod.php');

class uploadForm extends moodleform {
    function definition() {
        $mform = $this->_form; // Don't forget the underscore!
        $filemanageropts = $this->_customdata['filemanageropts'];
        // FILE MANAGER
        $mform->addElement('filemanager', 'attachments', 'File Manager Example', null, $filemanageropts);
        // Buttons
        $this->add_action_buttons();
    }
}


```


**File: upload.php**

Este arquivo é o responsável pela criação e gerência do formulário utilizado para este estudo.

Create some options for the file manager.
pt_br -  Definindo algumas opções para o "filemanager" você pode definir quais tipos de arquivos deseja permitir para upload e também o tamanho máximo. leia mais sobre os tipos de arquivos permitidos [aqui][listadearquivos] 


```php
$filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 50, 'context' => $context);
$customdata = array('filemanageropts' => $filemanageropts);
```
Create a new form object (found in classes/uploadForm.class.php).
pt_br neste momento ele cria o formulario usando a api moodleform e passa o $customdata como conjunto de opcoes para o filemanager

```php
$mform = new uploadForm('./upload.php?id='.$id, $customdata);
```

##### CONFIGURE FILE MANAGER

This is used to distinguish between multiple file areas, e.g. different student's assignment submissions, or attachments to different forum posts, in this case we use '0' as there is no relevant id to use.

```php
$itemid = 0;
``` 
Fetches the file manager draft area, called 'attachments'.
pt_br Obtem a area de rascunho do gerenciador de arquivos, chamada "attachments".

```php
$draftitemid = file_get_submitted_draft_itemid('attachments');
``` 
Copy all the files from the 'real' area, into the draft area. 
pt_br Copie todos os arquivos da area "real" onde estao salvos, para a area de rascunho.

```php
file_prepare_draft_area($draftitemid, $context->id, 'mod_uploadfile', 'attachment', $itemid, $filemanageropts);
``` 
Prepare the data to pass into the form - normally we would load this from a database, but, here, we have no 'real' record to load.
pt_br Prepare os dados para passar no formulario - normalmente devemos carregar isso a partir de um banco de dados, mas, aqui, nao temos registro "real" para carregar.

Add the draftitemid to the form, so that 'file_get_submitted_draft_itemid' can retrieve it.

```php
$entry = new stdClass();
$entry->attachments = $draftitemid;
``` 

Set form data
This will load the file manager with your previous files. 
pt_br neste momento ele preenche o formulário com o dado do upload já salvo anteriormente caso haja.

```php
$mform->set_data($entry);
``` 
Configurações comuns de um formulário moodle usando a api [Moodleform][moodleform]
```php
if ($mform->is_cancelled()) {
    // CANCELLED
    echo '<h1>Cancelled</h1>';
    echo '<p><p>';
    echo $OUTPUT->notification(format_string('Handle form cancel operation, if cancel button is present on form'));
    echo "<a href='./upload.php?id={$id}'><input type='button' value='Try Again' /><a>";
} else if ($data = $mform->get_data()) {
    // SUCCESS
    echo '<h1>Success!</h1>';
    echo '<p>In this case you process validated data. $mform->get_data() returns data posted in form.<p>';
    // Save the files submitted - Salva os arquivos submetidos do filemanager no banco de dados na tabela files.
    file_save_draft_area_files($draftitemid, $context->id, 'mod_uploadfile', 'attachment', $itemid, $filemanageropts);
} else {
    // FAIL / DEFAULT
    echo '<h1 style="text-align:center">Upload file</h1>';
    $mform->display();
}
``` 

**File: lib.php**

This is the most confusing part. For each plugin using a file manager will automatically look for this function. It always ends with _pluginfile. Depending on where you build your plugin, the name will change. In case, it is a local plugin called file manager.
pt_br - Esta é de fato a parte mais confusa de todo o processo, essa função costuma estar presente em seu plugin dentro do arquivo lib.php. Sempre que desejar manipular um arquivo o plugin irá automaticamente procurá-la. note que o mod_uploadfile logo após a palavra reservada function e em outros lugares adiante é uma referência do local e nome deste plugin e você deve substituir pelo local e nome do seu plugin, exemplo: ***mod_myplugin_pluginfile***(){...}.

```php
function mod_uploadfile_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB;
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    require_login();
    if ($filearea != 'attachment') {
        return false;
    }
    $itemid = (int)array_shift($args);
    if ($itemid != 0) {
        return false;
    }
    $fs = get_file_storage();
    $filename = array_pop($args);
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    $file = $fs->get_file($context->id, 'mod_uploadfile', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}
``` 

***File view.php***

get context page.
Inicialmente obtém-se no inicio da página o contexto.
```php
$context = context_system::instance();
```
Display Managed Files!
Para exibir às informações registradas pelo formulário!

```php
$fs = get_file_storage();
if ($files = $fs->get_area_files($context->id, 'mod_uploadfile', 'attachment', '0', 'sortorder', false)) {
    // Look through each file being managed - pt_br verificar todos os arquivos que estao sendo gerenciados pelo filemanager
    foreach ($files as $file) {
        // Build the File URL. Long process! But extremely accurate. - pt_br cria uma url para o arquivo
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        // Display link for file download - pt_bt exibe link para download do arquivo
        $download_url = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();
        echo '<a href="' . $download_url . '">' . $file->get_filename() . '</a><br/>';
        // Display for file - pt_bt exibe o arquivo em caso de imagem.
        if (file_extension_in_typegroup($file->get_filename(), 'web_image')) {
            echo html_writer::empty_tag('img', array('src' => $download_url));
        }
    }
} else {
    echo $OUTPUT->notification(format_string('Please upload an image first'));
}
```

If following the tutorial, does not get Success, install the plugin and debug to see its operation, sometimes we can forget to comment on something xD.
Caso ao seguir o tutorial, não obter Êxito, instale o plugin e depura para ver seu funcionamento, às vezes podemos nos esquecer de comentar sobre alguma coisa xD.


   [moodleform]: <https://docs.moodle.org/dev/Form_API>
   [moodlefile]: <https://docs.moodle.org/dev/File_API>
   [listadearquivos]: <https://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms>
