<?php
function tapp_get_latest_function($atts)
{
    // $options = tapp_options();
    // $attr = shortcode_atts(
    //     array(
    //         'total_post' => $options['tapp_totalpost']
    //     ),
    //     $atts,
    //     'foundry_top_posts'
    // );
    // if ($attr['total_post'] == "") {
    //     $tot = $options['tapp_totalpost'];
    // } else {
    //     $tot = $attr['total_post'];
    // }
    // $args = array(
    //     'post_type' => 'post',
    //     'orderby'   => 'meta_value_num',
    //     'meta_key'  => '_tapp_post_views',
    //     'orderby' => 'meta_value_num',
    //     'order' => 'DESC',
    //     'posts_per_page' => $tot  // Limit the number of posts to show will came from settings
    // );

    //start optimized code
    $options = tapp_options();
    $attr = shortcode_atts(
        array(
            'total_post' => $options['tapp_totalpost']
        ),
        $atts,
        'foundry_top_posts'
    );

    $tot = empty($attr['total_post']) ? $options['tapp_totalpost'] : $attr['total_post'];

    $cache_key = 'foundry_top_posts_' . $tot;
    $cached_posts = get_transient($cache_key);

    if (false === $cached_posts) {
        $args = array(
            'post_type'      => 'post',
            'orderby'        => 'meta_value_num',
            'meta_key'       => '_tapp_post_views',
            'order'          => 'DESC',
            'posts_per_page' => $tot
        );

        $the_query = new WP_Query($args);

        // Cache the results
        set_transient($cache_key, $the_query->posts, HOUR_IN_SECONDS);
    } else {
        $the_query = new WP_Query(array('post__in' => wp_list_pluck($cached_posts, 'ID')));
    }

    //end optimized code
    // Use $the_query as usual
    $the_query = new WP_Query($args);
    $tapp_onoffswitch = $options['tapp_onoffswitch'];
    if ($tapp_onoffswitch == "1") {
        if ($the_query->have_posts()) {
            echo '
            <style>
            .bx-wrapper{
                max-width: ' . esc_attr($options['tapp_sliderwidth']) . ';
                width: ' . esc_attr($options['tapp_sliderwidth']) . ';
                height: ' . esc_attr($options['tapp_sliderheight']) . ';
            }
            .bx-wrapper img {
                max-width: ' . esc_attr($options['tapp_sliderwidth']) . ';
                display: block;
                height: ' . esc_attr($options['tapp_sliderheight']) . ';
                width: ' . esc_attr($options['tapp_sliderwidth']) . ';
            }
            </style>
            <div class="top-post-container " id="slidebx">';
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $feat_image = wp_get_attachment_url(get_post_thumbnail_id());
                if ($feat_image != '') {
                    echo '<div><img src="' . esc_attr($feat_image) . '" alt="' . esc_attr(get_the_title()) . '" title="' . esc_attr(get_the_title()) . '"></div>';
                }
            }
            echo '</div>';
        }
    }
    wp_reset_postdata();
}
add_shortcode('tapp_get_latest', 'tapp_get_latest_function');

/* Shortcode For grid */
function tapp_get_latest_grid_function($atts)
{
    $options = tapp_options();
    $attr = shortcode_atts(
        array(
            'total_post' => $options['tapp_totalpost_grid']
        ),
        $atts,
        'foundry_top_posts'
    );
    if ($attr['total_post'] == "") {
        $tot = $options['tapp_totalpost_grid'];
    } else {
        $tot = $attr['total_post'];
    }
    $args = array(
        'post_type' => 'post',
        'orderby'   => 'meta_value_num',
        'meta_key'  => '_tapp_post_views',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'posts_per_page' => $tot
    );
    $result = '';
    $the_query = new WP_Query($args);
    $tapp_onoffswitch_grid = $options['tapp_onoffswitch_grid'];
    if ($tapp_onoffswitch_grid == "1") {
        if ($the_query->have_posts()) {
            $result .= '<div class="row article-row">';
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $feat_image = wp_get_attachment_url(get_post_thumbnail_id());
                if ($feat_image != '') {
                    $result .= '<div class="col-md-4  mb-10"><div class="innergrid"><div class="imggrid"><img src="' . $feat_image . '" alt="' . get_the_title() . '" title="' . get_the_title() . '"></div><div class="infogrid-content"><div class="titlegrid">' . get_the_title() . '</div><div class="contentgrid"><p>' . get_the_excerpt() . '</p></div><div class="btn-readmore"><a href="' . get_the_permalink() . '" class="redmorbtn">Read More</a></div></div></div></div>';
                }
            }
            $result .= '</div>';
        }
        return $result;
    }
    wp_reset_postdata();
}
add_shortcode('tapp_get_latest_grid', 'tapp_get_latest_grid_function');


function tapp_widget_enqueue_script()
{
    //print_r(plugin_dir_url(__FILE__) . 'inc/css/custom.css');
    // exit();
    wp_register_style('bxslider', plugin_dir_url(__FILE__) . 'css/jquery.bxslider.css', array(), '1.0.0');
    wp_register_style('bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), '1.0.0');
    wp_register_style('custom', plugin_dir_url(__FILE__) . 'inc/css/custom.css', array(), '1.0.0');
    wp_register_style('jquery-ui-min', plugin_dir_url(__FILE__) . '/inc/css/jquery-ui-min.css', array(), '1.0.0');

    if (isset($_GET['page']) && $_GET['page'] == TAPP_SLUG  && current_user_can('manage_options')) {
        wp_enqueue_style('bxslider');
        wp_enqueue_style('bootstrap');
        wp_enqueue_style('custom');
        wp_enqueue_style('jquery-ui-min');
    }

    wp_enqueue_script('bxslider-js', plugin_dir_url(__FILE__) . 'js/jquery.bxslider.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('custom', plugin_dir_url(__FILE__) . 'js/custom.js', array('bxslider-js'), '1.0.1', true);
}
add_action('wp_enqueue_scripts', 'tapp_widget_enqueue_script');

class tappTopPostWidget extends WP_Widget
{
    private $popPosts;
    /**
     * Sets up the widget
     *
     * @since 0.1
     */
    public function __construct()
    {
        parent::__construct(
            'gd-analytic-top-posts', // Base ID
            __('Top Posts', 'addweb-google-popular-post'), // Name
            array('description' => __('List Top posts', 'addweb-google-popular-post'),) // Args
        );
    }
    /**
     * Output the widget
     *
     *
     * @param array $args
     * @param array $instance
     */
    function widget($args, $instance)
    {
        extract($args);
        // $title = apply_filters('widget_title', empty($instance['title']) ? __('Recent ' . $posts_term, 'addweb-google-popular-post') : $instance['title'], $instance);

        $title = apply_filters(
            'widget_title',
            empty($instance['title'])
                ? /* translators: %s refers to the post type or term name (e.g., "posts"). */
                sprintf(__('Recent %s', 'addweb-google-popular-post'), $posts_term)
                : $instance['title'],
            $instance
        );


        /*
         * Start drawing the widget
         */
        //echo $before_widget;
        if ($title) {
            echo esc_attr($before_title . $title . $after_title);
        }
        $args = array(
            'post_type' => 'post',
            'orderby'   => 'meta_value_num',
            'meta_key'  => '_tapp_post_views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
        );
        $num_posts = (isset($instance['num_posts']) && !empty($instance['num_posts'])) ? $instance['num_posts'] : 5;
        if (isset($num_posts) && !empty($num_posts) && $num_posts > 1) {
            $args['posts_per_page'] = $num_posts;
        }
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            echo '<div class="top-post-container">';
            while ($the_query->have_posts()) {
                $the_query->the_post();
                echo '<div class="gd-toppost-item">';
                // echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
                echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
                echo '<div class="left">';

                if (has_post_thumbnail()) {
                    echo get_the_post_thumbnail(get_the_ID(), array(160, 100));
                } else {
                    $image = '';
                    echo '<img src="' . esc_url($image) . '" width="160" height="100" >';
                }
                echo '</div>';

                echo '</div>';
            }
            echo '</div>';
        }
        wp_reset_postdata();
        //echo $after_widget;
    }
    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form($instance)
    {
        $defaults = array(
            'title' => __('popular', 'addweb-google-popular-post'),
            'num_posts' => 5,
        );
        $instance = wp_parse_args((array) $instance, $defaults);
?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_attr('Title:', 'addweb-google-popular-post'); ?></label>
            <input id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($instance['title']); ?>" style="width:90%;" />
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('num_posts')); ?>"><?php echo esc_attr('Number of posts to show:', 'addweb-google-popular-post'); ?></label>
            <input id="<?php echo esc_attr($this->get_field_id('num_posts')); ?>" name="<?php echo esc_attr($this->get_field_name('num_posts')); ?>" value="<?php echo esc_attr($instance['num_posts']); ?>" style="width:90%;" type="number" />
        </p>

<?php
    }
    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['num_posts'] = intval($new_instance['num_posts']);
        return $instance;
    }
}
add_action('widgets_init', function () {
    register_widget('tappTopPostWidget');
});
