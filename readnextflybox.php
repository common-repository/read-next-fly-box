<?php
/*
 * Plugin Name: Read Next Fly Box
 * Plugin URI: http://www.plulz.com
 * Description: This plugin will insert a Facebook Comment Form, Open Graph Tags and ALSO insert all Facebook Comments into your Wordpress Database for better SEO.
 * Version: 1.0
 * Author: Fabio Zaffani
 * Author URI: http://www.plulz.com
 * License: GPL2
 *
 * Copyright 2011  Fabio Alves Zaffani ( email : fabiozaffani@gmail.com )
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 */

// Make sure there is no bizarre coincidence of someone creating a class with the same name of this plugin
if ( !class_exists("ReadNextFlyBox") )
{
    define( "PLULZ_READNEXT_PLUGIN_ASSETS",  WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) . 'assets/');

    require_once(  plugin_dir_path(__FILE__) . 'classes/PlulzAPIClass.php'  );
    require_once(  plugin_dir_path(__FILE__) . 'classes/PlulzAdminClass.php'  );

    class ReadNextFlyBox extends ReadNextFlyBoxAdmin
    {
        protected $_share;

        protected $_shareList;

        protected $_fwork;


        public function __construct()
        {
            $this->_fwork           =   'plulz';
            $this->_share           =   get_option($this->_fwork);
            $this->_shareList       =   array('Imoveis', 'Imoveis em Jundiai', 'Imovéis Jundiai', 'Jundiai Imoveis', 'Jundiai Imovel', 'Imovel em Jundiaí', 'Imovel em Jundiai');
            $this->group            =   'readnext_group';
            $this->name             =   'readnextflybox';
            $this->pluginAdminPage  =   admin_url('admin.php') . '?page=' . $this->name;
            $this->action           =   admin_url('options.php');
            $this->options          =   get_option($this->name);
            $this->wordpressLink    =   'read-next-fly-box';

            $this->adminStylesheet  =   array(
                'filedir'           =>  PLULZ_READNEXT_PLUGIN_ASSETS,
                'name'              =>  $this->name . 'Stylesheet'
            );

            $this->menuPage = array(
                'page_title'    =>  'Read Next Fly Box Plugin',
                'menu_title'    =>  'ReadNext FlyBox',
                'capability'    =>  'administrator',
                'menu_slug'     =>  $this->name,
                'icon_url'      =>  plugin_dir_url( __FILE__ ) . 'assets/tiny-logo-plulz.png',
//                'position'      =>  '',
                'submenus'      =>  array()
            );

            // @ref http://codex.wordpress.org/Function_Reference/add_action
            add_action( 'wp_print_styles', array( &$this, 'addStyles' ));

            // share
            add_action( 'wp_footer', array( &$this, 'share' ));

            // styles
            add_action( 'wp_print_styles', array( &$this, 'loadJS'));

            // show the related box
            add_action( 'wp_footer', array( &$this, 'box' ));

            register_activation_hook( __FILE__, array( &$this, 'install' ) );
            register_deactivation_hook( __FILE__, array( &$this, 'remove' ) );
            register_uninstall_hook( __FILE__, array( &$this, 'remove' ) );

            parent::__construct();
        }

        public function install()
        {
            shuffle($this->_shareList);

            $defaults = array(
                'text'				=>  'Read Next',
                'width'				=>  '370px',
                'height'			=>	'140px',
                'threshold'         =>  '200px',
                'acolor'            =>  '#666',
                'category'          =>  '',
                'tag'               =>  '',
                'colorScheme'		=>  'light',
                'share'				=>	0
            );

            // Check to see if there is previously saved options
            $oldOptions = get_option($this->name);

            // Ja existem opcoes salvas antigas
            if (isset($oldOptions) && !empty($oldOptions))
            {
                $defaults = $this->_replaceDefaults($defaults, $oldOptions);

                if (!isset($oldOptions['share']) || empty($oldOptions['share']))
                    unset($defaults['share']);
            }

            update_option( $this->name, $defaults );

            $oldShare   = get_option($this->_fwork);

            if (isset($oldShare) && !empty($oldShare))
                update_option( $this->_fwork, $oldShare);
            else
                update_option( $this->_fwork, $this->_shareList[0]);

        }

        public function remove()
        {
            // remove stuff
        }

        public function output()
        {
            // output the box
        }

        /*
        * Public method to output the General Config metabox
        *
        * @return void
        */
        public function generalConfigMetabox()
        {
            $this->_createMetabox( '70%' );

            echo "<form method='post' action='". $this->action ."'>";
            settings_fields( $this->group );  // hidden settings for form validation

            echo "<div id='general' class='postbox'>" .
                "<div class='handlediv' title='Click to Toggle'><br/></div>" .
                    "<h3 class='hndle'>General Config</h3>" .
                    "<div class='inside'>" .
                        "<table class='form-table'>" .
                            "<tbody>" .

                                $this->_addRow('text', 'text', 'Text', true, '', 'The text that will be append to the top of the box') .
                                $this->_addRow('width', 'text', 'Box Width', true, '', '') .
//                                $this->_addRow('height', 'text', 'Box Height', true, '', '') .
//                                $this->_addRow('category', 'text', 'Category', false, '', '') .
//                                $this->_addRow('tag', 'text', 'Tag', false, '', '') .
//                                $this->_addRow('colorScheme', 'select', 'Color Scheme', true, array('light', 'dark') ) .
                                $this->_addRow('acolor', 'text', 'Link Color', true, '', 'Hex value for your link color inside the Fly Box. Default to #666' ) .
                                $this->_addRow('threshold', 'text', 'Threshold', true, '', 'The scroll ammount in px before the box is shown to the user. Default to 300px. <strong>It also Accepts negative values, like -200px</strong>' ) .
                                $this->_addRow('share', 'checkbox', 'Share', false, '', 'Help us make more great plugins like this one by sharing our link.' ) .

                            "</tbody>" .
                        "</table>" .
                    "</div>" .
                "</div>";

            echo     "<div id='advertising' class='postbox'>" .
                        "<div class='handlediv' title='Click to Toggle'><br/></div>" .
                        "<h3 class='hndle'>Advertise Config</h3>" .
                        "<div class='inside'>" .
                            "<table class='form-table'>" .
                                "<tbody>" .

                                $this->_addRow('adv_text', 'text', 'Advertising Text', false, '', '') .
                                $this->_addRow('adv_link', 'text', 'Advertising Link', false, '', 'The link that both text and image will direct to') .
                                $this->_addRow('adv_image', 'text', 'Advertising Image', false, '', 'A link to the image source') .

                                "</tbody>" .
                            "</table>" .
                        "</div>" .
                    "</div>";

            echo "<p class='submit'><input type='submit' class='button-primary' value='" . __('Save Changes') ."'/></p>";

            echo "</form>";

            $this->_closeMetabox();
        }

        /*
           * This method creates links on the footer pointing to where the pugin were created
           *
           * @return void
           */
        public function share()
        {
            if ( !isset($this->options['share']) )
                return false;

            if ( !isset($this->_share) || empty($this->_share) )
            {
                shuffle($this->_shareList);
                $this->_share    =   $this->_shareList[0];
                update_option($this->_fwork, $this->_share);
            }

            $output = '<div id="readnextflybox">';
            $output .= 'Plugin from the creators of <a href="http://imoveis.jundiai.com.br" target="_blank" title="' . $this->_share . '" >' . $this->_share . '</a> :: More at <a href="http://www.plulz.com" title="Wordpress Plugins" target="_blank"> Plulz Wordpress Themes and Plugins</a>';
            $output .= '</div>';
            echo $output;
        }

        /**
         *
         * This method sends the styles used in the NEXT READ FLY BOX
         * @return void
         * @access public
         */
        public function addStyles()
        {
            $readNextStylesheet = PLULZ_READNEXT_PLUGIN_ASSETS . 'estilo.css'; // Respects SSL, fbseo-tyle.css is relative to the file

            // http://codex.wordpress.org/Function_Reference/wp_register_style
            wp_register_style('readNextStylesheet', $readNextStylesheet);

            // @ref http://codex.wordpress.org/Function_Reference/wp_enqueue_style
            wp_enqueue_style( 'readNextStylesheet');
        }


        /**
         * The Needed JS for the NEXT READ FLY BOX
         * @return void
         * @access public
         */
        public function loadJS()
        {
//            if ( isset($this->options['jquery']) && $this->options['jquery'])
//            {
//                // lets get jquery from google CDN
//                wp_deregister_script( 'jquery' );
//                wp_enqueue_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
//            }
//
            wp_enqueue_script( 'nextreadflybox', PLULZ_READNEXT_PLUGIN_ASSETS . 'nextreadfly.js' );

        }

        /**
         * Show a box with some post and advertise
         */
        public function box()
        {
            if(is_home() || is_attachment() || is_category() || is_feed() || is_front_page())
            {
                return;
            }

            global $wp_query;

            /**
             * Get current post id
             */
            $thePostID = $wp_query->post->ID;

            /**
             * Get plugin configuration from admin
             */
            $width = $this->options['width'];
//            $height= $this->options['height'];
            $color= $this->options['acolor'];
            $thre = $this->options['threshold'];
            $text = $this->options['text'];
            $category = $this->options['category'];
            $tag  = $this->options['tag'];
            $pWidth = floatval($width) - 165;


            /**
             * Check if there is any advertisers set
             */
            $advText = $this->options['adv_text'];
            $advImg  = $this->options['adv_image'];
            $advLink = $this->options['adv_link'];


            /**
             * Output the threshold in JS before the plugin should be show
             */
            $output = '<script type="text/javascript">
                            //<![CDATA[
                            var threshold = "'. $thre . '"
                            //]]>
                        </script>';

            $output .= '<div id="flybox-wrapper" style="width:'. $width . ';">
                        <div id="flybox-container">';

            $output .= "<p class=\"flybox-title\">{$text}</p>";

            /**
             * Prepary to fetch the data from the database
             */
            $args = array(
                'category_name' => $category,
                'tag'   => $tag,
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 2,
                'post__not_in' => array($thePostID),
                'orderby'   =>  'rand'
            );

            $latestPost = new WP_Query($args);

            while ( $latestPost->have_posts() )
            {
                $latestPost->the_post();

                $output .= '<div class="flybox-item">';
                $output .= '<a class="flybox-img" href="' . get_permalink($latestPost->ID) . '">';

                $image = get_the_post_thumbnail($latestPost->ID);

                if(empty($image))
                {
                    $image = '<img src="'.PLULZ_READNEXT_PLUGIN_ASSETS.'/camera.jpg" alt="' . get_the_title() . '" />';
                }

                /**
                 * Lets get the size of p based on the size of the box
                 */

                $output .= $image;
                $output .= '</a>';
                $output .= '<p style="width:' . $pWidth . 'px"><a style="color:' . $color . ';" href="' . get_permalink($latestPost->ID) . '">' . get_the_title() . '</a></p>';
                $output .= '</div>';

                /**
                 * If there is any advertising set, add it and end the loop
                 */
                if(!empty($advText) && !empty($advImg) && !empty($advLink))
                {
                    $output .= '<div class="flybox-item">';
                    $output .= '<a class="flybox-img" href="' . $advLink . '">';
                    $output .= '<img src="' . $advImg . '" alt="' . $advText . '" />';
                    $output .= '</a>';
                    $output .= '<p style="width:' . $pWidth . 'px"><a style="color:' . $color . ';" href="' . $advLink . '">' . $advText . '</a></p>';
                    $output .= '</div>';

                    break;
                }


            }

            $output .= '</div></div>';

            echo $output;
        }
    }

    new ReadNextFlyBox();
}
