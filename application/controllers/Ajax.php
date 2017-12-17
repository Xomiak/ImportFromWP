<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */

    private function getNewNum($table){
        $num = $this->db->select_max('num')->get($table)->result_array();
        if ($num[0]['num'] === NULL)
            return 0;
        else return ($num[0]['num'] + 1);
    }

    public function action($action){
        $this->load->library('Parse');

        $msg = '';

        if($action == 'getPostsCount'){
            $obj = new Parse();
            $postsCount = $obj->getAllPostsCount();
            $attachmentsCount = $obj->getAllAttachmentsCount();
            if($postsCount > 0) {
                echo 'Обнаружено '.$postsCount .' статей и '.$attachmentsCount .' файлов';
            } else echo '0';
        }
        elseif($action == 'getPostsCountOnly'){
            $obj = new Parse();
            $postsCount = $obj->getAllPostsCount();
            echo $postsCount;
        }
        /** Добавление поста и всего его хозяйства */
        elseif($action == 'getPost'){
            $db = $this->load->database('default', TRUE, TRUE);
            $obj = new Parse();
            //$from = post('from');
            $from = userdata('from');
            if(!$from) $from = 0;
            $post = $obj->getNextPost($from);
            if(!$post) { echo 'err'; die(); }
            set_userdata('from', ($post['ID'] + 1));

            $article = getArticleByUrl($post['post_name']);
            if(!$article) {
                /** Процесс сохранения статьи в нашу базу */
                $dbins = array(
                    'content' => $post['post_content'],
                    'name' => $post['post_title'],
                    'url' => $post['post_name'],
                    'parent_category_id' => 1,
                    'category_id' => '*1*',
                    'created_date' => $post['post_date'],
                    'active' => 1,
                    'num' => $this->getNewNum('articles')
                );
                $post_date = $post['post_date'];
                $pdArr = explode(' ', $post_date);
                if (isset($pdArr[0])) $dbins['date'] = $pdArr[0];
                //if(isset($pdArr[1])) $dbins['time'] = $pdArr[1];

                $added = $db->insert('articles', $dbins);

                $db->where('name', $dbins['name']);
                $db->where('url', $dbins['url']);
                $db->order_by('id', "DESC");
                $db->limit(1);
                $article = $db->get('articles')->result_array();

                if(isset($article[0])) {
                    $article = $article[0];
                    echo 'Добавлена статья <b>'.$article['name'].'</b><br/>';
                    /** Достаём из нашей базы добавленную статью */
                }
            } else {
                echo 'Найдена статья <b>'.$article['name'].'</b><br/>';
            }

                if($article){

                    /** ищем подвязанные файлы */
                    $dbins = array();
                    $attachments = $obj->getAttachmentsForPost($post['ID']);
                    if($attachments){
                        $imagesCount = 0;
                        $imgsArr = array();
                        foreach ($attachments as $attachment){
                            if(!in_array($attachment['guid'], $imgsArr)) {
                                //$attachment['guid'] = str_replace('http://favorit.od.ua','',$attachment['guid']);
                                if ($attachment['post_mime_type'] == 'application/pdf') {
                                    $dbins['pdf_file'] = $attachment['guid'];
                                    echo 'Найден подключённый PDF файл: ' . $dbins['pdf_file'] . '<br>';
                                } else if ($attachment['post_mime_type'] == 'image/gif'
                                    || $attachment['post_mime_type'] == 'image/jpeg'
                                    || $attachment['post_mime_type'] == 'image/jpg'
                                    || $attachment['post_mime_type'] == 'image/png'
                                    || $attachment['post_mime_type'] == 'image/bmp'
                                ) {
                                    $image = file_get_contents($attachment['guid']);
                                    if ($image) {
                                        $imgsArr[] = $attachment['guid'];
                                        $fileName = basename($attachment['guid']);
                                        $razdPos = strrpos($fileName, '.');
                                        /** @var имя файла $fName */
                                        $fName = substr($fileName, 0, $razdPos);
                                        $fName = translitRuToEn($fName);
                                        /** @var исходное расшерение $exc */
                                        $exc = substr($fileName, $razdPos + 1);
                                        /** @var конечное имя файла $fileName */
                                        $fileName = $fName . '.' . $exc;
                                        /** @var путь к корню $path */
                                        $path = $_SERVER['DOCUMENT_ROOT'];
                                        $siteUrl = userdata('site_url');
                                        if($siteUrl)
                                            $path = str_replace($_SERVER['SERVER_NAME'], $siteUrl, $path);
                                        /** @var путь к файлу от корня $inSitePath */
                                        $inSitePath = '/upload/articles/parse-'.date("Y-m-d").'/';
                                        if(!file_exists($path.$inSitePath))
                                            mkdir($path.$inSitePath);
                                        $imageFileIsOk = true;
                                        if(! file_exists($path . $inSitePath . $fName . '.jpg')) {
                                            /** сохраняем файл у нас */
                                            $imageFileIsOk = file_put_contents($path . $inSitePath . $fileName, $image);

                                            /** Если формат не JPG, то делаем перекодировку */
                                            //echo '<div style="color: red; font-weight:bolder">'.$exc.'</div>';
                                            if ($exc == 'jpeg' || $exc == 'bmp') {
                                                $imageFileIsOk = $converted = convertToJPG($path . $inSitePath . $fileName, $path . $inSitePath . $fName . '.jpg');
                                                @unlink($path . $inSitePath . $fileName);
                                                if ($converted) {
                                                    echo '<span style="color: Blue">Файл картинки ' . $inSitePath . $fileName . ' был перекодирован ' . $inSitePath . $fName . '.jpg' . ' в jpg!</span><br/>';
                                                    $fileName = $fName . '.jpg';
                                                }

                                            }
                                            if ($imageFileIsOk) {
                                                $attachment['guid'] = '/upload/articles/parse-' . date("Y-m-d") . '/' . $fileName;
                                                if ($imagesCount == 0) {
                                                    $dbins['image'] = $attachment['guid'];
                                                    echo 'Найдено и назначено основным фото: ' . $dbins['image'] . '<br />';
                                                    $imagesCount++;
                                                } else {
                                                    $imgs = array(
                                                        'image' => $attachment['guid'],
                                                        'article_id' => $article['id']
                                                    );
                                                    $db->insert('images', $imgs);
                                                    echo 'Найдено и добавлено доп. фото: ' . $imgs['image'] . '<br /><br>';
                                                }
                                            } else echo '<span style="color:red">Файл картинки повреждён, либо имеет не верный формат!</span><br/>';
                                        } else echo '<span style="color: green">Это изображение уже было загружено ранее!</span><br/>';
                                    }
                                }
                            } else echo '<span style="color: green">Это изображение уже было загружено ранее!</span><br/>';
                        }
                    }
                    /** если файлы найдены, сохраняем изменения */
                    if(count($dbins) > 0){
                        $db->where('id', $article['id'])->limit(1)->update('articles', $dbins);
                    }

                    /** ищем подвязанные категории и переформатируем их в тэги */
                    $tags = '';
                    $relations = $obj->getTermsByPostId($post['ID']);
                    if($relations){
                        foreach ($relations as $relation){
                            $term = $obj->getTerm($relation['term_taxonomy_id']);
                            if(($term) && $term['name'] != ''){
                                /** поиск тэга */
                                $name = '';
                                $db->where('url', $term['slug']);
                                $db->limit(1);
                                $tag = $db->get('tags')->result_array();
                                if(isset($tag[0]['name']) && $tag[0]['name'] != '' && $tag[0]['name'] != NULL){
                                    $name = $tag[0]['name'];
                                    $tag[0] = $tag;
                                    tagPlusPlus($name);
                                    echo 'Найден существующий тэг: '.$name.'<br/>';

                                } else{
                                    /** Добавляем новый тэг */
                                    $name = ucfirst(mb_strtolower(trim($term['name'])));
                                    if($name != '') {
                                        $dbins = array(
                                            'name' => $name,
                                            'url' => $term['slug'],
                                            'count' => 1
                                        );
                                        $db->insert('tags', $dbins);

                                        $tag = $db->where('url', $term['slug'])->limit(1)->get('tags')->result_array();
                                        if (isset($tag[0])) $tag = $tag[0];
                                        echo 'Найден и добавлен новый тэг: ' . $tag['name'] . '<br/>';
                                    } else echo 'Тэг НЕ был добавлен!';
                                }
                                if($name != ''){
                                    if($tags != '') $tags .= ', ';
                                    $tags .= $name;
                                } else echo 'Тэг НЕ был добавлен!';
                            }
                        }
                    }
                    /** Добавляем тэги к статье */
                    $ret = $db->where('id', $article['id'])->limit(1)->update('articles', array('tags' => $tags));
                    if($tags != '' && $ret != false)
                        echo '<span style="color:green">К статье добавлены тэги: '.$tags.'</span><br/>';
                }

            echo '<hr>';
            echo $msg;
        }
    }


}
