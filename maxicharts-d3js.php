<?php

/*
 * Plugin Name: MaxiCharts D3js Add-on
 * Plugin URI: https://wordpress.org/plugins/maxicharts-d3js-add-on/
 * Description: d3js v5 integration with specific chart types : network, sunburst and tree.
 * Author: MaxiCharts
 * Version: 1.1
 * Author URI: https://maxicharts.com/
 */
if (! defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}
//namespace BlueM\Tree\Serializer;
require 'd3jsSerializer.php';



if (! class_exists('MAXICHARTSAPI')) {
    define('MAXICHARTS_PLUGIN_PATH', plugin_dir_path(__DIR__));
    $toInclude = MAXICHARTS_PLUGIN_PATH . '/maxicharts/mcharts_utils.php';
    if (file_exists($toInclude)) {
        include_once ($toInclude);
    }
}

function MCD3JS_d3js_log($message)
{
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

class jsonItem {
    public $name = "";
    public $id = '';
    public $parent_id = 0;
    public $children = array();
    public function __construct($id, $name, $parent_id, $children)
    {
        $this->children = $children;
        $this->id = $id;
        $this->name = $name;
        $this->parent_id = $parent_id;
    }
}
/*
namespace BlueM\Tree\Serializer;
use BlueM\Tree;

class d3jsHierarchicalTreeJsonSerializer implements TreeJsonSerializerInterface
{
 
    private $childNodesArrayKey;

    public function __construct($childNodesArrayKey = 'children')
    {
        $this->childNodesArrayKey = $childNodesArrayKey;
    }

    public function serialize(Tree $tree): array
    {
        $data = [];
        foreach ($tree->getRootNodes() as $node) {
            $data[] = $this->serializeNode($node);
        }
        return $data;
    }
    private function serializeNode(Tree\Node $node): array
    {
        $nodeData = array_values($node->toArray());
        if ($node->hasChildren()) {
            $nodeData[$this->childNodesArrayKey] = [];
            foreach ($node->getChildren() as $child) {
                $nodeData[$this->childNodesArrayKey][] = $this->serializeNode($child);
            }
        }
        return $nodeData;
    }
}*/

/*
class d3jsHierarchicalTreeJsonSerializer implements \BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer {
    
    private function serializeNode(Tree\Node $node): array
    {
        $nodeData = array_values($node->toArray());
        if ($node->hasChildren()) {
            $nodeData[$this->childNodesArrayKey] = [];
            foreach ($node->getChildren() as $child) {
                $nodeData[$this->childNodesArrayKey][] = $this->serializeNode($child);
            }
        }
        return $nodeData;
    }
}*/

class MCD3JS_MaxiCharts_D3js
{

    public static $instance = NULL;

    public static $MCD3JS_d3js_logger = NULL;

    private $network_post_name = 'Network Node';

    protected $network_node_post_id = 'mchartsn_node';
    private $d3js_frontend_key = 'd3js-maxicharts-frontend-js';
    private $menu_position = 5;

    static function MCD3JS_getLogger($logger_category = "MCD3JS")
    {
        if (class_exists('MAXICHARTSAPI')) {
            /*MCD3JS_d3js_log("MAXICHARTSAPI logging to ");
            MCD3JS_d3js_log(MAXICHARTSAPI::getLogger()->getName());*/
            return MAXICHARTSAPI::getLogger($logger_category);
        } else {
            MCD3JS_d3js_log(__CLASS__ ." No MAXICHARTSAPI class");
        }
    }

    public function __construct()
    {
        add_action('init', array(
            $this,
            'create_network_node_type'
        ));
        
        add_shortcode('maxicharts_d3js', array(
            $this,
            'MCD3JS_fn'
        ));
        
        add_shortcode('maxicharts_d3js_nodes', array(
            $this,
            'MCD3JS_nodes_fn'
        ));
        /*
         * // tries to extract all possible dates inside html page and attach event descriptions to it
         * add_shortcode('maxicharts_d3js_html', array(
         * $this,
         * 'MCD3JS_html_fn'
         * ));
         */
        
        add_action('wp_enqueue_scripts', array(
            $this,
            'MCD3JS_frontend_stylesheet'
        ));
        
        add_action('wp_ajax_maxicharts_get_network_nodes', array(
            $this,
            'getPostsList'
        ));
        
        add_action('wp_ajax_nopriv_maxicharts_get_network_nodes', array(
            $this,
            'getPostsList'
        ));
        
        add_action('wp_ajax_maxicharts_get_form_as_tree', array(
            $this,
            'getGFFieldsAsJSONTree'
        ));
        
        add_action('wp_ajax_nopriv_maxicharts_get_form_as_tree', array(
            $this,
            'getGFFieldsAsJSONTree'
        ));
        
       
    }

    function create_network_node_type()
    {
        // self::MCTL_getLogger()->debug("create_network_node_type");
        $labels = array(
            'name' => '' . $this->network_post_name . 's',
            'singular_name' => '' . $this->network_post_name,
            'menu_name' => '' . $this->network_post_name . 's',
            'namemin_bar' => '' . $this->network_post_name,
            'add_new' => 'Add New',
            'add_new_item' => 'Add New ' . $this->network_post_name,
            'new_item' => 'New ' . $this->network_post_name,
            'edit_item' => 'Edit ' . $this->network_post_name,
            'view_item' => 'View ' . $this->network_post_name,
            'all_items' => 'All ' . $this->network_post_name . 's',
            'search_items' => 'Search ' . $this->network_post_name . 's',
            'parent_item_colon' => 'Parent ' . $this->network_post_name,
            'not_found' => 'No ' . $this->network_post_name . 's Found',
            'not_found_in_trash' => 'No ' . $this->network_post_name . 's Found in Trash'
        );
        
        /*
         * public => false, has_archive => false, publicaly_queryable => false, and query_var => false
         */
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_menu' => true,
            'show_inmin_bar' => true,
            'menu_position' => $this->menu_position,
            'menu_icon' => 'dashicons-marker',
            'capability_type' => 'post',
            // 'capability_type' => 'practitioner',
            'hierarchical' => true,
            'supports' => array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'comments',
                'page-attributes'
            ),
            'taxonomies' => array(
                'category',
                'post_tag'
            ),
            'has_archive' => false,
            'rewrite' => array(
                'slug' => 'medapp'
            ),
            'query_var' => false
        );
        
        register_post_type($this->network_node_post_id, $args);
    }

    function get_categories_hierarchical($args = array())
    {
        if (! isset($args['parent']))
            $args['parent'] = 0;
        
        $categories = get_categories($args);
        
        foreach ($categories as $key => $category) :
            
            $args['parent'] = $category->term_id;
            
            $categories[$key]->child_categories = $this->get_categories_hierarchical($args);
        endforeach
        ;
        
        return $categories;
    }

    
    protected function buildTree(array $elements, $parentId = 0) {
        /*$firstItem = array_pop(array_reverse($elements));
        $branch = array("name" => $firstItem->name,"children"=>array());
        */
        $branch = array();
        
        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                //$newItem = array("name" => $element->name,$element);
                //$branch[] = $newItem;
                
                
                //$branch["children"] = $newItem;//$element;   
                $branch[] = $element;
            }
        }
       
        return $branch;
    }
    
    public function getFormFields($form_id)
    {
        $this->MCD3JS_getLogger()->debug("Get form " . $form_id . " fields");
        if (empty($form_id)) {
            $this->MCD3JS_getLogger()->error("No ID specified ");
            $form_id = 1;
        }
        $list = array();
        $form = GFAPI::get_form($form_id);
        $form_fields = $form['fields'];
        
        foreach ($form_fields as $field) {
            
            if ($field['type'] == 'page') {
                continue;
            }
            $fieldTypesProcessingArray = array('radio','number','hidden','email','checkbox');
            
            if (!in_array($field['type'],$fieldTypesProcessingArray) ){
                continue;
            }
            
            //$this->MCD3JS_getLogger()->debug($field);
            //MCD3JS_d3js_log($field);
            /*
            $selected = '';
            $field_id = $field['id'];
            $field_label = ! empty($field['label']) ? $field['label'] : 'no label';
            $list[$field_id] = $field_label;
            */
            /*
             * if (empty($field ['label'])){
             * bulkusereditor_log ($field);
             * }
             */
            
            $nodeLabel = $field['id'] .' '.$field['label'];
            
            
            $item = array('id' => $field['id'] ,'value'=>$field['id'],'name' => $nodeLabel,'title' => $nodeLabel,'parent'=>0);
            //$item = new jsonItem($field['id'], $nodeLabel, 0, null);
            
            MCD3JS_d3js_log($nodeLabel);
            if (isset($field['conditionalLogic']) && isset($field['conditionalLogic']['actionType'])){                
                if ($field['conditionalLogic']['actionType'] == 'show' && isset($field['conditionalLogic']['rules'])){
                    $rules = $field['conditionalLogic']['rules'];
                    //MCD3JS_d3js_log($rules);
                    foreach ($rules as $rule){
                        //$firstRule = array_pop(array_reverse($rules));
                        $item['parent'] = $rule['fieldId'];
                        $choice = $rule['value'];
                        //MCD3JS_d3js_log($item['parent']);
                        $item['name'] = $choice . ' '.$nodeLabel;
                        $rows[] = $item;
                    }                    
                }                
            } else {
                MCD3JS_d3js_log("No conditionalLogic for ".$nodeLabel);
                // parent is root
                $rows[] = $item;
            }    
            
            // add choices as children
            if ($field['type'] == 'radio') {
                $nodeChoices = $field['choices'];    
                foreach ($nodeChoices as $choice){
                    $subItem = array('id' => $field['id'].'_'.$choice['value'] ,'name' => $choice['text'],'parent'=>$field['id']);
                    $rows[] = $subItem;
                }
                
            }
        }
        
        // $tree = new BlueM\Tree($rows);
        // create own serializer with array_values
        // $jsond3jsSerializer = new \BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer;
        $jsond3jsSerializer = new \BlueM\Tree\Serializer\D3jsHierarchicalTreeJsonSerializer;
        
        //$jsond3jsSerializer = new d3jsHierarchicalTreeJsonSerializer;
        $tree = new BlueM\Tree(
            $rows,
            ['jsonSerializer' => $jsond3jsSerializer,'rootId' => 0, 'id' => 'id', 'parent' => 'parent']
            );
        MCD3JS_d3js_log("Tree built!"); 
        
        /*
        $tree = $this->buildTree($rows);*/
        //
        //$jsonTree = json_encode($tree, JSON_FORCE_OBJECT);
       
        /*
        foreach ($tree->getNodes() as $node){
            // Get the number of Children
            $bool = $node->countChildren();
            $value = $node->get('parent');
            if ($value == 0 && $bool == 0){
                //MCD3JS_d3js_log($subOVal); 
                unset($node);
            }
        }*/
        
        $completeTree = ["name" => 'root','children'=>$tree];
        $oVal = (object)$completeTree;
        $jsonTree = json_encode($oVal);
        
        /*
        $this->MCD3JS_getLogger()->debug($jsonTree);
        MCD3JS_d3js_log($jsonTree); 
        */
        wp_send_json($jsonTree);
        
        // echo $list;
        die();
    }
    
    public function getGFFieldsAsJSONTree() {
        
        MCD3JS_d3js_log("getGFFieldsAsJSONTree");
        $form_id = sanitize_text_field($_POST['form_id']);
        return $this->getFormFields($form_id);
        
        /*
        $rows = array(
            array(
                'id' => 33,
                'parent_id' => 0,
            ),
            array(
                'id' => 34,
                'parent_id' => 0,
            ),
            array(
                'id' => 27,
                'parent_id' => 33,
            ),
            array(
                'id' => 17,
                'parent_id' => 27,
            ),
        );
        
        $tree = $this->buildTree($rows);
        MCD3JS_d3js_log($tree);
        
        wp_send_json(json_encode($tree));    */    
    }
    
    
    public function getPostsList()
    {
        $list = array();
        $nodes = array();
        $links = array();
        
        // get categories
        
        $categories = $this->get_categories_hierarchical();
        
        MCD3JS_d3js_log($categories);
        
        // get posts
        
        $post_type = sanitize_text_field($_POST['post_type']);
        // if ($post_type == 'both')
        $args = array(
            'numberposts' => - 1,
            'category' => 0,
            'orderby' => 'date',
            'order' => 'DESC',
            'include' => array(),
            'exclude' => array(),
            'meta_key' => '',
            'meta_value' => '',
            'post_type' => $post_type,
            'suppress_filters' => true
        );
        
        if (is_multisite()) {
            $blog_id = get_current_blog_id();
            switch_to_blog($blog_id);
            // $forms = GFAPI::get_forms ();
            $posts = get_posts($args);
            restore_current_blog();
        } else {
            $posts = get_posts($args);
            // $forms = GFAPI::get_forms ();
        }
        // MAXICHARTSAPI::getLogger()->trace(count($posts) . " posts retrieved");
        foreach ($posts as $post) {
            
            $start_date = $post->post_date;
            $linkToNode = get_field("link", $post->ID);
            
            $nodes[] = array(
                'id' => strval($post->ID),
                'r' => get_field("r", $post->ID),
                'start' => $start_date,
                'name' => $post->post_title,
                'title' => $start_date . ' : ' . $post->post_title,
                'group' => $post->post_status
            
            );
            self::MCD3JS_getLogger()->debug($linkToNode);
            MCD3JS_d3js_log($linkToNode);
            if ($linkToNode != null && ! empty($linkToNode[0])) {
                // node de node link
                $links[] = array(
                    "source" => strval($post->ID),
                    "target" => strval($linkToNode[0]->ID)
                );
            }
            
            $nodeParentCategoryArray = get_field("subnodes_taxonomy", $post->ID);
            MCD3JS_d3js_log("Node subcategory field:");
            MCD3JS_d3js_log($nodeParentCategoryArray);
            if ($nodeParentCategoryArray != null && is_array($nodeParentCategoryArray)) {
                // need to link all posts in this category to the current node
                foreach ($nodeParentCategoryArray as $catId) {
                    // current node could have multiple categories
                    MCD3JS_d3js_log("ooooo - Current network node has cat : " . $catId . " - " . get_cat_name($catId));
                    // FIXME : just get parent category
                    $myposts = get_posts(array(
                        'posts_per_page' => - 1,
                        // 'offset' => 1,
                        'post_type' => $this->network_node_post_id,
                        'status' => 'publish',
                        'category' => $catId
                    ));
                    
                    MCD3JS_d3js_log(count($myposts) . " other nodes in this category");
                    $index = 0;
                    foreach ($myposts as $postToLinkToParent) {
                        MCD3JS_d3js_log($index ++ . " " . $postToLinkToParent->post_title);
                        $terms = wp_get_post_terms($postToLinkToParent->ID, 'category');
                        $termsArray = array();
                        foreach ($terms as $selectedTerm) {
                            $termsArray[] = $selectedTerm->term_id;
                        }
                        
                        /*
                         * MCD3JS_d3js_log("terms for ".$postToLinkToParent->ID);
                         * MCD3JS_d3js_log($termsArray);
                         */
                        if (in_array($catId, $termsArray)) {
                            MCD3JS_d3js_log($postToLinkToParent->post_title . " / " . $postToLinkToParent->ID . " is in category of current node " . $post->post_title . " : " . $catId . " " . get_cat_name($catId) . " creating link...");
                            /*
                             * $term_id = $catId;
                             * $taxonomy_name = 'category';
                             * $termchildren = get_term_children($term_id, $taxonomy_name);
                             * MCD3JS_d3js_log($termchildren);
                             */
                            // if (empty($termchildren)) {
                            // if no children - direct category, add link
                            $links[] = array(
                                "source" => strval($post->ID),
                                "target" => strval($postToLinkToParent->ID)
                            );
                            // }
                        } else {
                            MCD3JS_d3js_log($postToLinkToParent->post_title . " has no category " . get_cat_name($catId));
                            MCD3JS_d3js_log($terms);
                            MCD3JS_d3js_log($termsArray);
                        }
                    }
                }
            }
        }
        
        $list["nodes"] = $nodes;
        $list["links"] = $links;
        
        // MCD3JS_d3js_log($list);
        
        wp_send_json($list);
        // wp_send($list);
        // echo $list;
    }

    public function MCD3JS_nodes_fn($attributes)
    {
        $a = shortcode_atts(array(
            'type' => $this->network_node_post_id, // post, page, both
            'data_path' => $this->network_node_post_id,
            'category' => ''
        ), $attributes);
        $type = isset($a['type']) ? $a['type'] : '';
        $category = isset($a['category']) ? $a['category'] : '';
        $data_path = isset($a['data_path']) ? $a['data_path'] : '';
        $this->MCD3JS_enqueue_scripts();
        $buttons = '<p class="maxicharts_d3js_button_bar">
  <input type="button" id="lastMonth" value="Last month"><br>
  <input type="button" id="lastYear" value="Last year"><br>
  <input type="button" id="lastFiveYears" value="Last 5 years"><br>
  <input type="button" id="fit" value="Fit all items"><br>
                
</p>';
        
        $result = $buttons;
        $result .= '<div id="visualization" data_path="' . esc_attr($data_path) . '" type="' . esc_attr($type) . '" category="' . esc_attr($category) . '"></div>';
        
        return $result;
    }

    public function MCD3JS_fn($attributes)
    {
        $result = '';
        
        $a = shortcode_atts(array(
            'data_path' => '',
            'gf_form_id' => '',
            'separator' => ';',
            'show_buttons' => true,
            'width' => '100%',
            'height' => '500px',
            'groups' => '',
            'type' => 'sunburst',
            'parent_col' => 'parent',
            'child_col' => 'child',
            'more_infos_1_col' => '',
            'more_infos_2_col' => '',
        ), $attributes);
        // parent_col="Parent" child_col="Chemin source" more_infos_1_col="Acces" more_infos_1_col="Remarques"
        $show_buttons = isset($a['show_buttons']) ? boolval($a['show_buttons']) : true;
        $data_path = isset($a['data_path']) ? $a['data_path'] : '';    
        $gf_form_id =  isset($a['gf_form_id']) ? $a['gf_form_id'] : '';    
        $separator = isset($a['separator']) ? $a['separator'] : '';
        $height = isset($a['height']) ? $a['height'] : '';
        $width = isset($a['width']) ? $a['width'] : '';
        $groups = isset($a['groups']) ? $a['groups'] : '';
        $type = isset($a['type']) ? $a['type'] : '';
        self::MCD3JS_getLogger()->debug($a);
        
        if ($type == "timeline") {
            if (empty($data_path)) {
                $data_path = plugins_url('/data/json/maxunger.json', __FILE__);
            }
        } else if ($type == "network") {
            if (empty($data_path)) {
                $data_path = plugins_url('/data/json/networkTest.json', __FILE__);
            }
        } else if ($type == "tree" /*|| $type == "gf_form_tree"*/ ) {
            $this->MCD3JS_enqueue_tree_scripts();
            if (is_numeric($gf_form_id) && $gf_form_id > 0){
                // data source is form data
                
            } else if (empty($data_path)) {
                $data_path = plugins_url('/data/json/flare.json', __FILE__);
            }
        }
        $this->MCD3JS_enqueue_scripts($type);
        //$d3jsKey = 'network-frontend-js';
        $localized_array = array_merge($a, array(
            'ajax_url' => admin_url('admin-ajax.php'),
            /*'height' => $height,
            'width' => $width,
            'groups' => $groups,
            'type' => $type,
            'gf_form_id' => $gf_form_id,*/
        ));
        
        self::MCD3JS_getLogger()->debug($localized_array);
        wp_localize_script($this->d3js_frontend_key, 'maxicharts_d3js_ajax_object', $localized_array);
                
        
        $buttons = '<p class="maxicharts_d3js_button_bar">
  <input type="button" id="lastMonth" value="Last month"><br>
  <input type="button" id="lastYear" value="Last year"><br>
  <input type="button" id="lastFiveYears" value="Last 5 years"><br>
  <input type="button" id="fit" value="Fit all items"><br>  
</p>';
        
        if ($show_buttons) {
            $result .= $buttons;
        }
        //$result .= '<div id="visualization" type="' . esc_attr($type) . '" data_path="' . esc_url($data_path) . '" separator="' . esc_attr($separator) . '"></div>';
        $result .= '<div id="visualization"></div>';
        
        return $result;
    }

    function MCD3JS_enqueue_tree_scripts($type = null)
    {
        // js
        wp_enqueue_script('maxicharts_d3js_dndTree_script');
        wp_enqueue_script('maxicharts_d3js_hierarchy');
        wp_enqueue_script('maxicharts_d3js_request');
        
        // css
        wp_enqueue_style('mcd3-font-awesome-css');
    }

    function MCD3JS_enqueue_scripts($type = null)
    {
        if (1) {            
            wp_enqueue_script('maxicharts_d3js_script');
        } else  if ($type == "tree") {
            wp_enqueue_script('maxicharts_d3js_v3_script');
        } else {
            wp_enqueue_script('maxicharts_d3js_v4_script');
        }
        
        $d3jsKey = 'd3js-maxicharts-frontend-js';
        wp_enqueue_script($d3jsKey);
        
        wp_enqueue_style('d3js-css');
        
    }

    function MCD3JS_frontend_stylesheet()
    {
        self::MCD3JS_getLogger()->debug("MCD3JS_frontend_stylesheet...");
        // wp_register_script( string $handle, string|bool $src, array $deps = array(), string|bool|null $ver = false, bool $in_footer = false )
        
        /*
        // d3js core lib V4
        $d3js_script = plugins_url('/libs/d3js/d3.v4.min.js', __FILE__);
        wp_register_script('maxicharts_d3js_v4_script', $d3js_script);
        
        // d3js core lib V3
        $d3js_script = plugins_url('/libs/d3js/d3.v3.min.js', __FILE__);
        wp_register_script('maxicharts_d3js_v3_script', $d3js_script);
        */
        
        $d3js_script = plugins_url('/libs/node_modules/d3/dist/d3.min.js', __FILE__);
        wp_register_script('maxicharts_d3js_script', $d3js_script);
        
        // collapsible tree
        $d3js_script_tree = plugins_url('/js/dndTree.js', __FILE__);
        wp_register_script('maxicharts_d3js_dndTree_script', $d3js_script_tree, array(), "7880033", false);
        
        // standard d3 hierarchy charts
        // node_modules/d3-hierarchy/dist/d3-hierarchy.min.js 
        $d3js_hierarchy_script = plugins_url('/libs/node_modules/d3-hierarchy/dist/d3-hierarchy.min.js', __FILE__);        
        wp_register_script('maxicharts_d3js_hierarchy',$d3js_hierarchy_script);
        
        $d3js_request_script = plugins_url('/libs/node_modules/d3-request/build/d3-request.min.js', __FILE__);
        wp_register_script('maxicharts_d3js_request',$d3js_request_script);
        
        $d3js_maxicharts_js = plugins_url('/js/maxicharts-d3js-frontend.js', __FILE__);
        $d3jsKey = $this->d3js_frontend_key;
        self::MCD3JS_getLogger()->debug("+-+-+-+-+-+-");
        $pluginDeps = array(
            'jquery'
        );
        // $pluginDeps = array_merge($pluginDeps, $jsKeys);
        self::MCD3JS_getLogger()->debug($pluginDeps);
        self::MCD3JS_getLogger()->debug("+-+-+-+-+-+-");
        
        wp_register_script($d3jsKey, $d3js_maxicharts_js, $pluginDeps, false, true);
        self::MCD3JS_getLogger()->debug("JS loaded : " . $d3jsKey . ' ' . $d3js_maxicharts_js);
        
        wp_localize_script($d3jsKey, 'maxicharts_d3js_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        
        wp_register_style('d3js-css', plugins_url('/css/maxicharts-d3js.css', __FILE__));
        /*
         * wp_register_style('network-vis-css', plugins_url('/libs/node_modules/vis/dist/vis-network-graph2d.min.css', __FILE__));
         */
        self::MCD3JS_getLogger()->debug("...MCD3JS_frontend_stylesheet");
        
        // jQuery CSV
        
        // var/www/ilurn.com/wp-content/plugins/maxicharts-network/libs/jquery-csv-0.8.9/src
        
        // jquery.csv.min.js
        $jsquery_csv_version = '0.8.9';
        $network_jquery_csv_js = plugins_url('/libs/jquery-csv-' . $jsquery_csv_version . '/src/jquery.csv.min.js', __FILE__);
        wp_register_script('network-jquery-csv', $network_jquery_csv_js);
        
        
        $font_awsome_cdn = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css";
        wp_register_style('mcd3-font-awesome-css', $font_awsome_cdn);
        
    }
    // maxicharts_d3js_d3js_script
}

new MCD3JS_MaxiCharts_D3js();