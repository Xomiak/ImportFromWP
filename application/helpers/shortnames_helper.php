<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function getUri()
{
    return $_SERVER['REQUEST_URI'];
}

function getTemplateName()
{
    if (!isAdminPath()) {
        if (!defined('TEMPLATE'))
            define(TEMPLATE, 'default');
        if (!file_exists(X_PATH.'/application/views/' . TEMPLATE . '/')) {
            die("No Template Folder: ".TEMPLATE."!");
        }
        return TEMPLATE;
    } else {
        if (!defined('TEMPLATE'))
            define('TEMPLATE', 'admin');
        return TEMPLATE;
    }
}

function getHead($meta = false)
{
    $metaArr = array(
        'title' => '',
        'description' => '',
        'keywords' => '',
        'h1' => '',
        'robots' => '',

    );
    if (!$meta && $_SERVER['REQUEST_URI'] == '/') {
        $meta = getOptionsByModule('meta');
        // vdd($meta);
        $data = array();
        $postfix = "";
        if (isset($GLOBALS['multilanguage']) && $GLOBALS['multilanguage'] == TRUE) {            // Если включена мультиязычность
            $current_lang = $GLOBALS['current_lang'];
            $default_lang = $GLOBALS['default_lang'];

            //if ($current_lang != $default_lang) {
            $postfix = '_' . $current_lang;
            $defPostfix = '_' . $default_lang;
            //vdd($postfix);
            //}
        }

        // var_dump($meta['main_title' . $postfix]);


        if (isset($meta['main_title' . $postfix])) $metaArr['title'] = $meta['main_title' . $postfix];
        else $metaArr['title'] = $meta['main_title' . $defPostfix];
        if (isset($meta['main_keywords' . $postfix])) $metaArr['keywords'] = $meta['main_keywords' . $postfix]; else $metaArr['keywords'] = $meta['main_keywords' . $defPostfix];
        if (isset($meta['main_description' . $postfix])) $metaArr['description'] = $meta['main_description' . $postfix]; else $metaArr['description'] = $meta['main_description' . $defPostfix];
        if (isset($meta['main_robots' . $postfix])) $metaArr['robots'] = $meta['main_robots' . $postfix]; else $metaArr['robots'] = $meta['main_robots' . $defPostfix];
        if (isset($meta['main_h1' . $postfix])) $metaArr['h1'] = $meta['main_h1' . $postfix]; else $metaArr['h1'] = $meta['main_h1' . $defPostfix];
        if (isset($meta['next'])) $metaArr['next'] = $meta['next'];
        if (isset($meta['prev'])) $metaArr['prev'] = $meta['prev'];

        //if (isset($meta['seo' . $postfix])) $data['seo'] = $meta['main_seo' . $postfix]; else $data['seo'] = $meta['main_seo'.$defPostfix];
        //   if (isset($meta['content' . $postfix])) $data['content'] = $meta['main_content' . $postfix]; else $data['content'] = $meta['main_content'];


        if (!isset($meta['adding_scripts'])) $data['adding_scripts'] = $metaArr['adding_scripts'] = '';
        else $data['adding_scripts'] = $meta['adding_scripts'];

        $GLOBALS['metaArr'] = $metaArr;

    } elseif (!$meta){
        $metaArr = getGlobalMeta();
    } else $metaArr = $meta;
    ob_start();
    $template = getTemplateName();
    //vdd($template);
    $file_head = '/application/views/' . $template . '/common/head.php';

    if (myfile_exists($file_head)) {
//vdd($metaArr);
        myinclude($file_head, $metaArr);
    } else error("Нет файла " . $file_head, 1, 'shortnames_helper.php', '32');

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

function getHeader()
{
    ob_start();
    $template = getTemplateName();
    if (myfile_exists('/application/views/' . $template . '/common/header.php'))
        myinclude('/application/views/' . $template . '/common/header.php');

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

function getPopups()
{
    ob_start();
    $template = getTemplateName();
    if (myfile_exists('/application/views/' . $template . '/common/popups.php'))
        myinclude('/application/views/' . $template . '/common/popups.php');

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

function getBlock($name)
{
    ob_start();
    $template = getTemplateName();
    if (myfile_exists('/templates/' . $template . '/blocks/' . $name . '.php'))
        myinclude('templates/' . $template . '/blocks/' . $name . '.php');

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

function getFooter($adding_scripts = '')
{
    ob_start();

    $template = getTemplateName();
    if (myfile_exists('/application/views/' . $template . '/common/footer.php'))
        myinclude('/application/views/' . $template . '/common/footer.php');

    $output = ob_get_contents();
    ob_end_clean();
    $output = str_replace('[adding_scripts]',$adding_scripts, $output);

    return $output;
}

function getStyles()
{

    ob_start();


    $path = X_PATH.'/application/views/' . TEMPLATE . '/common/styles.php';

    if (file_exists($path))
        myinclude($path);

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

function getScripts()
{
    ob_start();
    $template = getTemplateName();
    // var_dump($template);
    $path = X_PATH.'/application/views/' . $template . '/common/scripts.php';
    if (file_exists($path))
        myinclude($path);

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}


function getConfig($name)
{
    $CI = &get_instance();
    return $CI->config->item($name);
}

function getDomain()
{
    return $_SERVER['SERVER_NAME'];
}

function post($name)
{
    if (isset($_POST[$name]))
        return $_POST[$name];
    else
        return false;
}

function postIf($name)
{
    if (isset($_POST[$name]))
        return $_POST[$name];
    else
        return '';
}

function getModel($model)
{
    $CI = &get_instance();

    $model = 'Model_' . $model;

    $CI->load->model($model);
    return $CI->$model;
}

// DATABASE //
function getItemById($id, $table)
{
    $model = getModel('sql');
    return $model->getById($id, $table);
}

function updateItem($id, $table, $dbins)
{
    $CI = &get_instance();
    return $CI->db->where('id', $id)->limit(1)->update($table, $dbins);
}

function getItemBy($by, $value, $table)
{
    $model = getModel('sql');
    return $model->getBy($by, $value, $table);
}

// * DATABASE * //

function loadLibrary($name)
{
    $CI = &get_instance();
    $CI->load->library($name);
}

function loadHelper($name)
{
    if (strpos($name, '_helper') === false)
        $name .= '_helper';

    $CI = &get_instance();
    $CI->load->helper($name);
}

function insertInDb($table, $dbins, $returnData = true)
{
    $CI = &get_instance();
    $CI->db->insert($table, $dbins);
    if ($returnData) {
        foreach ($dbins as $key => $value) {
            $CI->db->where($key, $value);
        }
        $result = $CI->db->get($table)->result_array();
        if (isset($result[0])) return $result[0];
        else return false;
    }
}

function getFieldtypeByName($name)
{
    $CI = &get_instance();
    $CI->load->model('Model_fieldtypes', 'ft');
    return $CI->ft->getByName($name);
}

function get_no_get()
{
    $back = $_SERVER['REQUEST_URI'];
    $strpos = strpos($back, '?');
    if ($strpos) {
        $back = substr($back, 0, $strpos);
    }
    return $back;
}

function request_uri($noPagination = false, $noGetParams = false)
{
    $CI = &get_instance();
    $uri = $CI->uri->uri_string();
    if ($uri == "") $uri = "/";
    else $uri = "/" . $uri . "/";
    if (($noPagination) && strpos($uri, '/page-')) {
        $pos = strpos($uri, '/page-');
        $res = substr($uri, 0, $pos);
        $pos = strpos($uri, '/', $pos + 1);
        if ($pos) {
            $res .= substr($uri, $pos);
        }
        $uri = $res;
    }

    if ($noGetParams) {
        $pos = strpos($uri, '?');
        if ($pos) {
            $uri = substr($uri, 0, $pos);
        }
    }
    return $uri;
}

function getOption($name, $full = false, $notId = false)
{

    $CI = &get_instance();
    $ret = $CI->model_options->getOption($name, $full);
    //if($name == 'link_vk'){vdd($ret);}
    if ($ret === false) return false;
    else return $ret;
}

function getOptionByLang($name, $full = false, $notId = false)
{
    $ret = false;
    $current_lang = strtolower(getCurrentLanguage());
    $default_lang = strtolower(getDefaultLanguageCode());
    if ($current_lang != $default_lang)
        $ret = getOption($name . '_' . $current_lang, $full, $notId);
    if (!$ret)
        $ret = getOption($name, $full, $notId);

    return $ret;
}

function getOptionsByModule($module)
{
    $CI = &get_instance();
    $options = $CI->model_options->getOptionsByModule($module);
    if ($options) {
        $ret = array();
        foreach ($options as $item) {
            $ret[$item['name']] = $item['value'];
        }
    }
    return $ret;
}

function getOptionArray($name, $childArray = false)
{
    $optArr = array();
    $ret = getOption($name);
    if ($ret) {
        $ret = explode('|', $ret);
        if ($ret) {
            foreach ($ret as $item) {
                $item = explode('=', $item);
                if (is_array($item) && count($item) > 1) {
                    $optArr[] = array(
                        'name' => $item[0],
                        'value' => $item[1]
                    );
                } else $optArr[] = $item[0];
            }
        }
    }
    return $optArr;
}

function userdata($name)
{
    $CI = &get_instance();

    return $CI->session->userdata($name);
}

function set_userdata($name, $value)
{
    $CI = &get_instance();
    return $CI->session->set_userdata($name, $value);
}

function unset_userdata($name)
{
    $CI = &get_instance();

    return $CI->session->unset_userdata($name);
}

function back_no_get()
{
    $back = $_SERVER['REQUEST_URI'];
    $strpos = strpos($back, '?');
    if ($strpos) {
        $back = substr($back, 0, $strpos);
    }
    if (!$back)
        $back = '/';
    redirect($back);
}

function vd($value)
{
    echo '<pre>';
    print_r($value);
    echo '</pre>';
}

function vdd($value)
{
    vd($value);
    die();
}

function alert($value)
{
    ?>
    <script>
        alert('<?=$value?>');
    </script>
    <?php
}

