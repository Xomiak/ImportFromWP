<?php
/**
 * Created by PhpStorm.
 * User: XomiaK
 * Date: 15.06.2017
 * Time: 12:45
 */

$CI = & get_instance();

class Parse
{
    /**
     * Достаём следующую запись
     * @return mixed - запись, либо false
     */
    public function getNextPost($from){
        $CI = & get_instance();
        $wp = $CI->load->database('wp', TRUE, TRUE);

        $wp->where('ID >', $from);
        $wp->where('post_status', 'publish');
        $wp->where('ping_status', 'open');
        $wp->where('post_type', 'post');
        $wp->where('post_title <>', 'Черновик');
        $wp->where('post_title <>', '');
        $wp->where('post_title <>', NULL);
        $wp->where('post_content <>', '');
        $wp->order_by('ID','ASC');
        $wp->limit(1);
        $post = $wp->get('wp_posts')->result_array();
        if(isset($post[0])) {
            set_userdata('from', $post[0]['ID']);
            return $post[0];
        } else return false;
    }

    /**
     * Достаём категорию
     *
     * @param $id
     * @return mixed
     */
    public function getTerm($id){
        $CI = & get_instance();
        $wp = $CI->load->database('wp', TRUE, TRUE);
        $term = $wp->where('term_id', $id)->limit(1)->get('terms')->result_array();
        if(isset($term[0])) $term = $term[0];
        return $term;
    }

    /**
     * Достаём привязку к категориям
     *
     * @param $id   - ID поста
     * @return mixed
     */
    public function getTermsByPostId($id){
        $CI = & get_instance();
        $wp = $CI->load->database('wp', TRUE, TRUE);
        $wp->where('object_id', $id);
        $relations = $wp->get('term_relationships')->result_array();
        return $relations;
    }

    /**
     * Достаём все прикреплённые к посту файлы
     *
     * @param $post_id
     * @return mixed
     */
    public function getAttachmentsForPost($post_id){
        $CI = & get_instance();
        $wp = $CI->load->database('wp', TRUE, TRUE);
        $wp->where('post_status', 'inherit');
        $wp->where('post_parent', $post_id);
        $wp->where('post_type', 'attachment');
        $wp->order_by('ID','ASC');
        $posts = $wp->get('posts')->result_array();
        return $posts;
    }


    /**
     * Получаем кол-во записей
     * @return int
     */
    public function getAllPostsCount(){
        $CI = & get_instance();
        $wp = $CI->load->database('wp', TRUE, TRUE);
        $wp->where('post_status', 'publish');
        $wp->where('ping_status', 'open');
        $wp->where('post_type', 'post');
        $wp->where('post_title <>', 'Черновик');
        $wp->where('post_content <>', '');
        $posts = $wp->count_all_results('posts');
        return $posts;
    }

    /**
     * Получаем все записи
     * @return mixed
     */
    public function getAllPosts(){
        $CI = & get_instance();
        $wp = $CI->load->database('wp', TRUE, TRUE);
        $wp->where('post_status', 'publish');
        $wp->where('ping_status', 'open');
        $wp->where('post_type', 'post');
        $wp->where('post_title <>', 'Черновик');
        $wp->where('post_content <>', '');
        $wp->order_by('ID','ASC');
        $posts = $wp->get('posts')->result_array();
        return $posts;
    }

    /**
     * Получаем кол-во прикреплённых файлов
     * @return int
     */
    public function getAllAttachmentsCount(){
        $CI = & get_instance();
        $wp = $CI->load->database('wp', TRUE, TRUE);
        $wp->where('post_status', 'inherit');
        //$wp->where('ping_status', 'open');
        $wp->where('post_type', 'attachment');
        $posts = $wp->count_all_results('posts');
        return $posts;
    }

    /**
     * Получаем все прикреплённые файлы
     * @return mixed
     */
    public function getAllAttachments(){
        $CI = & get_instance();
        $wp = $CI->load->database('wp', TRUE, TRUE);
        $wp->where('post_status', 'inherit');
        //$wp->where('ping_status', 'open');
        $wp->where('post_type', 'attachment');
        $wp->order_by('ID','ASC');
        $posts = $wp->get('posts')->result_array();
        return $posts;
    }
}