<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function getGlobalMeta(){
    $lang = getCurrentLanguageCode();
    $meta = array(
        'title'     => getOption('global_title_'.$lang),
        'keywords'     => getOption('global_keywords_'.$lang),
        'description'     => getOption('global_description_'.$lang)
    );
    return $meta;
}
function generateMeta($meta){
    $lang = getCurrentLanguageCode();

    if(!isset($meta['title'])) $meta['title'] = ''; elseif (isset($meta['name'])) $meta['title'] = $meta['name'];
    if(!isset($meta['keywords'])) $meta['keywords'] = ''; elseif (isset($meta['keywords'])) $meta['keywords'] = $meta['name'];
    if(!isset($meta['description'])) $meta['description'] = ''; elseif (isset($meta['description'])) $meta['description'] = $meta['name'];

    $gTitle = getOption('global_title_'.$lang);
    $gKeywords = getOption('global_keywords_'.$lang);
    $gDescription = getOption('global_description_'.$lang);
    $gRobots = getOption('global_robots_'.$lang);
    $gSeparator = getOption('title_separator');

    if($meta['title'] != '') $meta['title'] .= ' '.$gSeparator.' ';
    if($meta['keywords'] != '') $meta['keywords'] .= ', ';
    if($meta['description'] != '') $meta['description'] .= '. ';

    $meta['title'] .= $gTitle;
    $meta['keywords'] .= $gKeywords;
    $meta['description'] .= $gDescription;
//die($gRobots);
    if($gRobots == 'noindex, nofollow')
        $meta['robots'] = $gRobots;

    return $meta;
}

function isAdminPath(){
    if(strpos($_SERVER['REQUEST_URI'],'admin') !== false)
        return true;
    return false;
}

function myinclude($path, $config = false){
    if(!$config && isset($GLOBALS['metaArr']))
        $config = $GLOBALS['metaArr'];
//var_dump($config);
    if(strpos($path, X_PATH) !== false)
        $path = str_replace(X_PATH,'',$path);
    $path = X_PATH . $path;

    $path = str_replace('//', '/', $path);
    $path = str_replace('newtemplates','new/templates', $path);
    //var_dump($path);die();

    $GLOBALS['config'] = $config;

    //var_dump($path);
    include($path);
}

function myfile_exists($path){

    $path = X_PATH . $path;
    $path = str_replace('//', '/', $path);
    //   var_dump(file_exists($path));die();
    return file_exists($path);
}

function error($msg, $err_no = false, $file = false, $line = false){
    echo '<div class="error">';
    if($err_no) echo '<p>Ошибка №'.$err_no.'</p>';
    if($msg) echo '<p>'.$msg.'</p>';
    if($file) echo '<p>В файле: '.$file.'</p>';
    if($line) echo '<p>Строка №'.$line.'</p>';
    echo '</div>';
}

function getProtocol(){ // http или https
    $result = 'http';
    $isHttps = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']);
    if($isHttps) $result = 'https';

    return $result;
}

function exit_status($str, $params = false){
    echo json_encode(array('status'=>$str,'params'=>$params));
    exit;
}

function addLog($action, $text, $type = 'client', $error = ""){
    $CI = & get_instance();
    $dbins = array(
        'date'	=> date("Y-m-d"),
        'time'	=> date("H:i"),
        'unix'	=> time(),
        'ip'	=> getRealIp(),
        'login'	=> userdata('login'),
        'type'	=> $type,
        'error'	=> $error,
        'action'	=> $action,
        'text'	=> $text
    );
    $CI->db->insert('logs', $dbins);
}

function getAdressArray($adress){
    $CI = & get_instance();
    $adrarr = explode('|', $adress);
    $ret = array();
    $retCount = 0;
    if(is_array($adrarr)) {
        if (count($adrarr) > 1) {
            foreach ($adrarr as $one) {
                //vdd($one);
                $onearr = explode("\n", $one);
                if (is_array($onearr)) {
                    foreach ($onearr as $item) {
                        $itemarr = explode('=', $item);
                        if (is_array($itemarr)) {
                            if (isset($itemarr[0]) && isset($itemarr[1])) {
                                $param = $itemarr[0];
                                $value = $itemarr[1];
                                $ret[$retCount][$param] = $value;
                            }
                        }
                    }
                    $retCount++;
                }
            }
        } else if (count($adrarr) == 1 && strpos($adress, ';') !== false) {
            $adrarr = explode(';', $adress);
            if (is_array($adrarr)) {
                foreach ($adrarr as $one) {
                    $ret[$retCount]['adress'] = $one;
                    $retCount++;
                }
            }
        } else $ret[0]['adress'] = $adress;
    }
    return $ret;
}

function isBot(&$botname = ''){
    /* Эта функция будет проверять, является ли посетитель роботом поисковой системы */
    $bots = array(
        'rambler','googlebot','aport','yahoo','msnbot','turtle','mail.ru','omsktele',
        'yetibot','picsearch','sape.bot','sape_context','gigabot','snapbot','alexa.com',
        'megadownload.net','askpeter.info','igde.ru','ask.com','qwartabot','yanga.co.uk',
        'scoutjet','similarpages','oozbot','shrinktheweb.com','aboutusbot','followsite.com',
        'dataparksearch','google-sitemaps','appEngine-google','feedfetcher-google',
        'liveinternet.ru','xml-sitemaps.com','agama','metadatalabs.com','h1.hrn.ru',
        'googlealert.com','seo-rus.com','yaDirectBot','yandeG','yandex',
        'yandexSomething','Copyscape.com','AdsBot-Google','domaintools.com',
        'Nigma.ru','bing.com','dotnetdotcom'
    );
    foreach($bots as $bot)
        if(stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false){
            $botname = $bot;
            return true;
        }
    return false;
}

function createXmlMarkersForGoogleMaps($article){
    $xmlFile = false;
    $xml = "";
    if($article['adress'] != '' && $article['adress'] != NULL){
        $adress = getAdressArray($article['adress']);
        if(is_array($adress)){
            $xml .= '<?xml version="1.0"?>
<markers>';
            foreach ($adress as $item){
                $xml .= '<marker>';
                if(isset($item['name'])) $xml .= '<name>'.$item['name'].'</name>';
                if(isset($item['adress'])) $xml .= '<address>'.$item['adress'].'</address>';
                if(isset($item['lat'])) $xml .= '<lat>'.$item['lat'].'</lat>';
                if(isset($item['lng'])) $xml .= '<lng>'.$item['lng'].'</lng>';
                $xml .= '</marker>';
            }
            $xml .= '</markers>';

            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/upload/maps_xml/'.$article['id'].'.xml', $xml);
        }
    }
}

function checkEmail($email) {
    if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email))
    {
        return true;
    }
    return false;
}

function clearCache()
{
    $folder = 'application/cache';
    if (is_dir($folder)) {

        $handle = opendir($folder);
        while ($subfile = readdir($handle)) {
            if ($subfile == '.' || $subfile == '..' || $subfile == '.htaccess' || $subfile == 'index.html') continue;
            else {
                @unlink("{$folder}/{$subfile}");
            }

        }
        @closedir($handle);
        if (@rmdir($folder)) return true;
        else return false;
    } else {
        if (@unlink($folder)) return true;
        else return false;
    }
    return false;
}

function getCategoryLevel($category)
{
    $level = 0;
    if($category['parent'] > 0){
        $CI = & get_instance();
        while ($category['parent'] != 0){
            $level++;
            $category = $CI->model_categories->getCategoryById($category['parent']);
        }
    }
    return $level;
}

function getCategoryByLevel($level, $category)
{
    $CI = & get_instance();
    $now = getCategoryLevel($category);
    while($category['parent'] > 0 && $level < $now){
        $now--;
        $category = $CI->model_categories->getCategoryById($category['parent']);
    }
    return $category;
}

function get_extension($file_name){
    $ext = explode('.', $file_name);
    $ext = array_pop($ext);
    return strtolower($ext);
}


function check_smartphone() {

    $phone_array = array('iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'cellphone', 'opera mobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser');
    $agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );

    foreach ($phone_array as $value) {

        if ( strpos($agent, $value) !== false ) return true;

    }

    return false;

}

function debug($item)
{
    if($_SERVER['REMOTE_ADDR'] == '195.138.64.78')
    {
        vd($item);
    }
}

function getRandCode($chars_min = 5, $chars_max = 10, $use_upper_case = false, $include_numbers = true, $include_special_chars = false) {
    $length = rand($chars_min, $chars_max);
    $selection = 'aeuoyibcdfghjklmnpqrstvwxzQWERTYUIOPASDFGHJKLZXCVBNM';
    if ($include_numbers) {
        $selection .= "1234567890";
    }
    if ($include_special_chars) {
        $selection .= "!@\"#$%&[]{}?|";
    }

    $password = "";
    for ($i = 0; $i < $length; $i++) {
        $current_letter = $use_upper_case ? (rand(0, 1) ? strtoupper($selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))];
        $password .= $current_letter;
    }

    return $password;
}

function itsFirstVisit(){
    if(userdata('im_back') == true)
        return false;

    return true;
}

function form_action($data)
{
    $CI = & get_instance();
}


function GetRealIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}



function getLangs()
{
    $CI = & get_instance();
    $langs = explode('|', $CI->model_options->getOption('languages'));
    for ($i = 1; $i < count($langs); $i++) {
        $langs[$i] = trim($langs[$i]);
    }
    return $langs;
}

function getFileType($url)
{
    $ret = $url;
    $pos = strrpos($url, '.');
    if ($pos) {
        $ret = substr($ret, $pos + 1);
    }
    return $ret;
}

function arrayDelNulled($array)
{
    $ret = array();
    $r = 0;
    if (is_array($array)) {
        $c = count($array);
        for ($i = 0; $i < $c; $i++) {
            if (trim($array[$i]) != '') {
                $ret[$r] = trim($array[$i]);
                $r++;
            }
        }
    }
    return $ret;
}

function arrayDelRows($arr, $start, $end = false)
{
    $start = $start - 1;
    $new = array();
    $new_i = 0;
    $count = count($arr);
    if(!$end) $end = $count;
    else $end = $end  - 1;
    for($i = 0; $i < $count; $i++)
    {
        if($i < $start || $i > $end){
            $new[$new_i] = $arr[$i];
            $new_i++;
        }
    }
    //vd($new);die();
    return $new;
}

function getFullUrl($element, $langParam = true){

    //vdd($element);
    $url = '';
    if (isset($element['category_id'])) {
        $arr = explode('|', $element['category_id']);
        if(isset($arr[0]))
            $element['category_id'] = str_replace('*','', $arr[0]);
        else $element['category_id'] = str_replace('*','', $element['category_id']);
        if (isset($element['url_cache']) && !empty($element['url_cache']))
            return getUrl($element['url_cache']);
        else {
            $CI = & get_instance();
            $cat = $CI->model_categories->getCategoryById($element['category_id']);
            if ($cat['parent'] != 0)
                $url .= getFullUrl($CI->model_categories->getCategoryById($cat['parent']), false);
            $url .= (($cat['url'][0] != '/') ? '/' : '') . $cat['url'] . (($element['url'][0] != '/') ? '/' : '') . $element['url'];
            $url .= ($url[strlen($url) - 1] != '/') ? '/' : '';
            $url = preg_replace('([/]+)', '/', $url);
            switch ($cat['type']) {
                case 'articles':
                    $CI->model_articles->updateUrlCache($element['id'], $url);
                    break;
                case 'products':
                    $CI->model_products->updateUrlCache($element['id'], $url);
                    break;
            }
        }
    } elseif (isset($element['parent'])) {
        if ($element['parent'] != 0) {
            $CI = & get_instance();
            $url .= getFullUrl($CI->model_categories->getCategoryById($element['parent']), $langParam);
            $url .= (($element['url'][0] != '/') ? '/' : '') . $element['url'];
        } else
            $url .= (($element['url'][0] != '/') ? '/' : '') . $element['url'];

        $url .= ($url[strlen($url) - 1] != '/') ? '/' : '';
        $url = preg_replace('([/]+)', '/', $url);
    }
    elseif (isset($element['type']) && $element['type'] == 'gallery')
    {
        $url .= '/gallery';
        $url .= (($element['url'][0] != '/') ? '/' : '') . $element['url'].'/';
    }


    if($langParam) return getUrl($url);
    else return $url;
    //return ($langParam) ? getUrl($url) : $url;
}

function validate($data, $result_type, $validValues = false, $mayBeEmpty = false, $defaultValue = '')
{
    switch ($result_type) {
        case 'number':

            if (!isset($data))
                return false;
            $data = trim($data);
            if (empty($data)) {
                if (!empty($defaultValue)) {
                    $data = $defaultValue;
                    break;
                } else {
                    if ($mayBeEmpty) {
                        $data = intval($data);
                    } else
                        return false;
                }
            }


            if (!is_numeric($data)) {
                return false;
            }
            if ($validValues !== false && is_array($validValues) && !empty($validValues) && !in_array($data, $validValues, true)) {
                return false;
            }
            break;

        case 'string':
            $quotes = array("\x27", "\x22", "\x60", "\t", "\n", "\r", "*", "%", "<", ">", "?", "!");
            $goodquotes = array("-", "+", "#");
            $repquotes = array("\-", "\+", "\#");
            $data = trim(strip_tags($data));
            $data = str_replace($quotes, '', $data);
            $data = str_replace($goodquotes, $repquotes, $data);
            $data = str_replace(" +", " ", $data);
            $data = htmlspecialchars($data);
            $data = strval($data);
            if (empty($data) && !empty($defaultValue)) {
                $data = $defaultValue;
                break;
            }
            if (!$mayBeEmpty && empty($data)) {
                return false;
            }
            if ($validValues !== false && is_array($validValues) && !empty($validValues) && !in_array($data, $validValues, true)) {
                return false;
            }
            break;
    }

    return $data;
}

function getPrimaryCategory($id){
    $CI = & get_instance();
    $cat = $CI->model_categories->getCategoryById($id);
    while($cat['parent'] != 0){
        $cat = $CI->model_categories->getCategoryById($id);
    }
    return $cat['id'];
}


function tagPlusPlus($tag){
    $CI = & get_instance();
    $db = $CI->load->database('default', TRUE, TRUE);
    $tag = $db->where('name', $tag)->limit(1)->get('tags')->result_array();
    if(isset($tag[0])){
        $count = $tag[0]['count'] + 1;
        $db->where('id', $tag[0]['id'])->limit(1)->update('tags', array('count' => $count));
    }
}

function translitRuToEn ($string)
{
    $r_trans = array(
        "а","б","в","г","д","е","ё","ж","з","и","й","к","л","м",
        "н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","э",
        "ю","я","ъ","ы","ь"," ",",","(",")","\"","А","Б","В","Г",
        "Д","Е","Ё","Ж","З","И","Й","К","Л","М","Н","О","П","Р",
        "С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Э","Ю","Я","Ъ","Ы",
        "Ъ","/","«","»","!","&","'",":","#","$","№","?","…","+","@","%","*",
        "і","І","є","Є","Ь"
    );

    $e_trans = array(
        "a","b","v","g","d","e","e","j","z","i","i","k","l","m",
        "n","o","p","r","s","t","u","f","h","c","ch","sh","sch",
        "e","yu","ya","","i","","-","-","-","","","a","b","v","g",
        "d","e","Yo","g","z","i","i","k","l","m","n","o","p","r",
        "s","t","u","f","h","c","Ch","Sh","Sch","e","Yu","Ya","",
        "i","","","","","","ft","","","no","s","nomber","","","-plus-","","","",
        "i","i","e","e",""
    );

    $string = str_replace($r_trans, $e_trans, $string);

    $string = str_replace('--','-',$string);
    $string = str_replace('--','-',$string);
    $string = str_replace('--','-',$string);
    $string = str_replace('--','-',$string);
    $string = str_replace('--','-',$string);



    $string = preg_replace('/.[^a-z0-9-]+/is', '', $string);

    return $string;
}

function convertToJPG($originalImage, $outputImage, $quality = 75){
    // jpg, png, gif or bmp?
    $exploded = explode('.',$originalImage);
    $ext = $exploded[count($exploded) - 1];

    if (preg_match('/jpg|jpeg/i',$ext))
        $imageTmp=imagecreatefromjpeg($originalImage);
    else if (preg_match('/png/i',$ext))
        $imageTmp=imagecreatefrompng($originalImage);
    else if (preg_match('/gif/i',$ext))
        $imageTmp=imagecreatefromgif($originalImage);
    else if (preg_match('/bmp/i',$ext))
        $imageTmp=@imagecreatefromwbmp($originalImage);
    else
        return false;

    // quality is a value from 0 (worst) to 100 (best)
    if($imageTmp) {
        @imagejpeg($imageTmp, $outputImage, $quality);
        @imagedestroy($imageTmp);
    } else return false;
    return true;
}

function getArticleByUrl($url){
    $CI = & get_instance();
    $db = $CI->load->database('default', TRUE, TRUE);
    $article = $db->where('url', $url)->limit(1)->get('articles')->result_array();
    if(isset($article[0])) return $article[0];

    return false;
}