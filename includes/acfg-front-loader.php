<?php

if (!class_exists('ACFG_Front_Loader')) {
    class ACFG_Front_Loader
    {

        /**
         * 'acf-on-the-go' constructor.
         *
         * The main plugin actions registered for WordPress
         */
        public function __construct()
        {

            $this->hooks();
            
        }

        /**
         * Initialize
         */
        public function hooks()
        {
            add_action('init', array($this, 'register_filters'));
            add_action('wp_ajax_scrap_it', array($this, 'acfg_update_fields'));
            add_action('wp_ajax_nopriv_scrap_it', array($this, 'acfg_update_fields'));
        }

        /**
         * Renders text fields and text areas with additional html that allows to target these areas via javascript
         * @param  [String] $value
         * @param  [Int] $post_id
         * @param  [Object] $field 
         * @return [String] returns edited value with additional html
         * 
         * @since    1.0.0
         */
        public function acfg_selector($value, $post_id, $field)
        {
            if (strpos($value, 'http') === 0 || $value == '#' || $value == '' || filter_var($value, FILTER_VALIDATE_EMAIL) || is_admin()) {
                $value = $value;
            } else {
                $match_field = $field['wrapper']['class'];
                if (strpos($match_field, 'acfgo') !== false) {
                    $field_id = $field['ID'];
                    $field_label = $field['label'];
                    $field_type = $field['type'];
                    $key = $field['key'];
                    $label = $field['name'];
                    $pen_icon = ACFG_URL . "assets/img/pencil12.png";
                    $type = 'labas';
                    $value = "<span class='acf-onthego-content-wrapper'>
                        <span class='acf-onthego' data-field-id=" . $field_id . " data-field-type=" . $field_type . " data-postid=" . $post_id . " data-name=" . $label . " data-key=" . $field['key'] . ">$value</span>
                        <a data-id='#" . $field_id . "' data-field-label=" . $field_label . " class='acfg-editor acfg-dialog' href='JavaScript:void(0);'>
                            <img height='12' width='12' src='$pen_icon'>
                        </a>
                        <div id=" . $field_id . " class='acfg-dialogbox' data-field-id=" . $field_id . " data-field-type=" . $field_type . " data-field-label=" . $field_label . " data-postid=" . $post_id . " data-name=" . $label . " data-key=" . $field['key'] . ">
                            <textarea class='acfg-inner-content' rows='4' cols='50'>$value</textarea>
                        </div>
                    </span>";
                }
                return $value;
            }
        }

        /**
         * Renders wysiwyg fields with additional html that allows to target these areas via javascript
         * @param  [String] $value
         * @param  [Int] $post_id
         * @param  [Object] $field 
         * @return [String] returns edited value with additional html
         *
         * @since    1.0.0
         */
        public function acfg_textarea_selector($value, $post_id, $field)
        {
            $field_id = $field['ID'];
            $field_type = $field['type'];
            $field_label = $field['label'];
            $pen_icon = ACFG_URL . "assets/img/pencil12.png";
            $key = $field['key'];
            $label = $field['name'];

            $match_field = $field['wrapper']['class'];
                if (strpos($match_field, 'acfgo') !== false) {
                    $field_id = $field['ID'];
                    $field_label = $field['label'];
                    $field_type = $field['type'];
                    $key = $field['key'];
                    $label = $field['name'];
                    $pen_icon = ACFG_URL . "assets/img/pencil12.png";
                    $type = 'labas';
                    $value = "<span class='acf-onthego-content-wrapper'>
                        <span class='acf-onthego' data-field-id=" . $field_id . " data-field-type=" . $field_type . " data-postid=" . $post_id . " data-name=" . $label . " data-key=" . $field['key'] . ">$value</span>
                        <a data-id='#" . $field_id . "' data-field-label=" . $field_label . " class='acfg-editor acfg-dialog' href='JavaScript:void(0);'>
                            <img height='12' width='12' src='$pen_icon'>
                        </a>
                        <div id=" . $field_id . " class='acfg-dialogbox' data-field-id=" . $field_id . " data-field-type=" . $field_type . " data-field-label=" . $field_label . " data-postid=" . $post_id . " data-name=" . $label . " data-key=" . $field['key'] . ">
                            <textarea class='acfg-inner-content' rows='4' cols='50'>$value</textarea>
                        </div>
                    </span>";
                }
            return $value;
        }

        /**
         * Formats field value to html if there is any
         * @param  [String] $value
         * @param  [Int] $post_id
         * @param  [Object] $field 
         * @return [String] returns edited value
         *
         * @since    1.0.0
         */
        public function acfg_format_value($value, $post_id, $field)
        {
            $value = html_entity_decode($value);
            return $value;
        }

        /**
         * Registers filters required for ACF field rendering
         * @since 2.0.0
         */
        public function register_filters()
        {

            if (is_user_logged_in() && !is_admin() && current_user_can('edit_posts')) {
                add_filter('acf/load_value/type=text',  array($this, 'acfg_selector'), 10, 3);
                add_filter('acf/load_value/type=textarea', array($this, 'acfg_textarea_selector'), 10, 3);
            }
        }

        /**
         * Updates edited ACF fields in the database
         * 
         * @since 1.0.0
         */
        public function acfg_update_fields()
        {
            $result = array();
            $field_name = $field_content = '';
            if (isset($_REQUEST)) {

                if (is_user_logged_in()) {

                    $textArr  = sanitize_text_field($_REQUEST['textArr']);

                    foreach ($textArr as $arr) {
                        $field_key  = $arr[0];
                        $field_content = $arr[1];
                        $field_name = $arr[2];
                        $current_postid = $arr[3];
                    }
                    // update_field($name, $text, $postid);
                    $old_field_value = get_field($field_name, $current_postid, true);
                    $updated_data = update_field($field_name, $field_content, $current_postid);

                    if ($old_field_value == $field_content) {
                        $error_string1 = _e('Nothing to change', ACFG_TEXTDOMAIN);
                        $result =  array(
                            'status' => 'no-changes',
                            'message' => $error_string1,
                        );
                        // exit;
                    } else {
                        $error_string2 = _e('Updated Successfully', ACFG_TEXTDOMAIN);
                        $result = array(
                            'status' => 'success',
                            'message' => $error_string2,
                            'field_key' => $field_key,
                            'field_content' => $field_content,
                        );
                    }

                    $data = json_encode($result);
                    echo $data;
                }
            }

            die();
        }
    }
}

new ACFG_Front_Loader();
