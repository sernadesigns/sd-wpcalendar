<?php
/*
Plugin Name: SmartStart Calendar
Plugin URI:
Description: Display post data from any post type (even custom post types) in a clean, responsive calendar. Choose to display data on the post's publish date or an alternate date, or even display separate data on both dates.
Author: Michael Serna
Author URI: http://sernadesigns.com
Version: 1.0.1
License: GPLv3 or higher
*/

define('TODAY', current_time( 'timestamp' ));

add_action( 'wp_enqueue_scripts','smartstart_calendar_style');
function smartstart_calendar_style(){
    wp_enqueue_style( 'smartstart-calendar-style',  plugins_url( 'sd-wpcalendar.css', __FILE__ ) );
}

function create_holidays_list($year) {
    $easterDays = easter_days($year); // # days after March 21st
    $holidays = array();

    if ( get_calendar_setting( 'new_year' ) == 'yes' ) :
        $holidays[$year.'-01-01'] = 'New Year\'s Day';
    endif;
    if ( get_calendar_setting( 'martin_luther_king' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-01 third monday"))] = 'Martin Luther King Jr. Day';
    endif;
    if ( get_calendar_setting( 'groundhog' ) == 'yes' ) :
        $holidays[$year.'-02-02'] = 'Groundhog Day';
    endif;
    if ( get_calendar_setting( 'lincoln' ) == 'yes' ) :
        $holidays[$year.'-02-12'] = 'Lincoln\'s Birthday';
    endif;
    if ( get_calendar_setting( 'valentines' ) == 'yes' ) :
        $holidays[$year.'-02-14'] = 'Valentine\'s Day';
    endif;
    if ( get_calendar_setting( 'presidents' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-02 third monday"))] = 'President\'s Day';
    endif;
    if ( get_calendar_setting( 'st_patricks' ) == 'yes' ) :
        $holidays[$year.'-03-17'] = 'St. Patrick\'s Day';
    endif;
    if ( get_calendar_setting( 'easter' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-03-21 +$easterDays days"))] = 'Easter';
    endif;
    if ( get_calendar_setting( 'april_fools' ) == 'yes' ) :
        $holidays[$year.'-04-01'] = 'April Fool\'s Day';
    endif;
    if ( get_calendar_setting( 'earth' ) == 'yes' ) :
        $holidays[$year.'-04-22'] = 'Earth Day';
    endif;
    if ( get_calendar_setting( 'mothers' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-05 second sunday"))] = 'Mother\'s Day';
    endif;
    if ( get_calendar_setting( 'memorial' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-06-01 last monday"))] = 'Memorial Day';
    endif;
    if ( get_calendar_setting( 'flag' ) == 'yes' ) :
        $holidays[$year.'-06-14'] = 'Flag Day';
    endif;
    if ( get_calendar_setting( 'fathers' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-06 third sunday"))] = 'Father\'s Day';
    endif;
    if ( get_calendar_setting( 'independence' ) == 'yes' ) :
        $holidays[$year.'-07-04'] = 'Independence Day';
    endif;
    if ( get_calendar_setting( 'patriot' ) == 'yes' ) :
        $holidays[$year.'-09-11'] = 'Patriot Day';
    endif;
    if ( get_calendar_setting( 'labor' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-09 first monday"))] = 'Labor Day';
    endif;
    if ( get_calendar_setting( 'bosses' ) == 'yes' ) :
        $holidays[$year.'-10-16'] = 'Bosses\' Day';
    endif;
    if ( get_calendar_setting( 'halloween' ) == 'yes' ) :
        $holidays[$year.'-10-31'] = 'Halloween';
    endif;
    if ( get_calendar_setting( 'columbus' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-10 second monday"))] = 'Columbus Day';
    endif;
    if ( get_calendar_setting( 'all_saints' ) == 'yes' ) :
        $holidays[$year.'-11-01'] = 'All Saints\' Day';
    endif;
    if ( get_calendar_setting( 'veterans' ) == 'yes' ) :
        $holidays[$year.'-11-11'] = 'Veterans Day';
    endif;
    if ( get_calendar_setting( 'thanksgiving' ) == 'yes' ) :
        $holidays[date("Y-m-d",strtotime($year."-11 fourth thursday"))] = 'Thanksgiving Day';
    endif;
    if ( get_calendar_setting( 'christmas_eve' ) == 'yes' ) :
        $holidays[$year.'-12-24'] = 'Christmas Eve';
    endif;
    if ( get_calendar_setting( 'christmas' ) == 'yes' ) :
        $holidays[$year.'-12-25'] = 'Christmas Day';
    endif;
    if ( get_calendar_setting( 'kwanzaa' ) == 'yes' ) :
        $holidays[$year.'-12-26'] = 'Kwanzaa';
    endif;
    if ( get_calendar_setting( 'new_year_eve' ) == 'yes' ) :
        $holidays[$year.'-12-31'] = 'New Year\'s Eve';
    endif;

    return $holidays;
}

/**
 * ==CALENDAR POST TYPE
 * Register a Custom Post Type (Calendar)
 * ============================================================================================================= */
add_action('init', 'calendar_entry_init');
function calendar_entry_init() {
    register_post_type('calendar-entry', array(
        'labels' => array(
            'name' => 'Calendar Entries',
            'singular_name' => 'Calendar Entry',
            'add_new' => 'Add New', 'calendar',
            'add_new_item' => 'Add New Calendar Entry',
            'edit_item' => 'Edit Calendar Entry',
            'new_item' => 'New Calendar Entry',
            'view_item' => 'View Calendar Entry',
            'search_items' => 'Search Calendar Entries',
            'not_found' => 'No calendar entries found',
            'not_found_in_trash' => 'No calendar entries found in Trash',
            'parent_item_colon' => '',
            'menu_name' => 'Calendar Entries'
        ),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_menu' => true,
        'rewrite' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 20,
        'supports' => array( 'title', 'editor', 'excerpt', 'custom-fields' )
    ) );
}

/**
 * ==CALENDAR POST TYPE (Rewrite Flush)
 * This prevents 404 errors when viewing custom post archives
 * ( Always do this whenever introducing a new post type or taxonomy )
 * ============================================================================================================= */
function calendar_post_rewrite_flush(){
    calendar_entry_init();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'calendar_post_rewrite_flush' );

/**
 * ==CALENDAR ENTRY QUERY
 * PullS calendar entries from the 'calendar-entry' post type
 * ============================================================================================================= */
function get_calendar_entries( $currentDate, $the_post_types ) {
    $calendar_entry_query = new WP_Query( array(
        'post_type' => 'calendar-entry',
        'post_status' => 'any',
        'posts_per_page' => -1,
    ) );
    if( $calendar_entry_query->have_posts() ): ?>
        <dl>
        <?php
        while( $calendar_entry_query->have_posts() ): $calendar_entry_query->the_post();
            $obj = get_post_type_object( get_post_type( get_the_ID() ) );
            $post_to_calendar = get_post_meta( get_the_ID(), 'Calendar Note', true );
            if( $currentDate == get_the_date( 'Ymd' ) ) : ?>
            <dt><strong><?php echo $obj->labels->singular_name; ?>:</strong> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></dt>
            <dd><?php echo $post_to_calendar; ?></dd>
            <?php endif;
        endwhile; ?>
        </dl>
        <?php
        wp_reset_postdata();
    endif;
}

/**
 * ==CALENDAR PUBLISH DATE ENTRY QUERY
 * PullS calendar entries from any post type that is set to post on the publish date
 * ============================================================================================================= */
function get_publish_date_entries( $currentDate, $the_post_types ) {
    $post_date_array = array();
    foreach ( $the_post_types as $the_post_type ) :
        if ( get_calendar_setting( $the_post_type ) == 'yes' AND $the_post_type != 'calendar-entry' ) :
            array_push($post_date_array, $the_post_type );
        endif;
    endforeach;
    $post_date_query = new WP_Query( array(
        'post_type' => $post_date_array,
        'post_status' => array(
            'publish', 'future'
            ),
        'order' => 'ASC',
        'orderby' => 'date',
        'posts_per_page' => -1,
    ) );
    if( $post_date_query->have_posts() ): ?>
        <dl>
        <?php
        while( $post_date_query->have_posts() ): $post_date_query->the_post();
            $obj = get_post_type_object( get_post_type( get_the_ID() ) );
            $post_to_calendar = get_post_meta( get_the_ID(), 'Calendar Note', true );
            if( $currentDate == get_the_date( 'Ymd' ) AND $post_to_calendar ) : ?>
            <dt><strong><?php echo $obj->labels->singular_name; ?>:</strong> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></dt>
            <dd><?php echo $post_to_calendar; ?></dd>
            <?php endif;
        endwhile; ?>
        </dl>
        <?php
        wp_reset_postdata();
    endif;
}

/**
 * ==CALENDAR ALTERNATE DATE QUERY
 * PullS calendar entries from any post type that has an alternate calendar date set
 * ============================================================================================================= */
function get_alternate_date_entries( $currentDate, $the_post_types ) {
    $alternate_date_array = array();
    foreach ( $the_post_types as $the_post_type ) :
        if ( get_calendar_setting( $the_post_type ) == 'yes' AND $the_post_type != 'calendar-entry' ) :
            array_push($alternate_date_array, $the_post_type );
        endif;
    endforeach;
    $alternate_date_query = new WP_Query( array(
        'post_type' => $alternate_date_array,
        'post_status' => array(
            'publish', 'future'
            ),
        'order' => 'ASC',
        'meta_key' => 'Calendar Date (alternate)',
        'orderby' => 'meta_value_num',
        'posts_per_page' => -1,
    ) );
    if( $alternate_date_query->have_posts() ): ?>
        <dl>
        <?php
        while( $alternate_date_query->have_posts() ): $alternate_date_query->the_post();
            $obj = get_post_type_object( get_post_type( get_the_ID() ) );
            $calendar_note2 = get_post_meta( get_the_ID(), 'Calendar Note (alternate)', true );
            $calendar_date2 = get_post_meta( get_the_ID(), 'Calendar Date (alternate)', true );
            if( $currentDate == date( 'Ymd', strtotime($calendar_date2) ) AND $calendar_note2 ) : ?>
            <dt><strong><?php echo $obj->labels->singular_name; ?>:</strong> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></dt>
            <dd><?php echo $calendar_note2; ?></dd>
            <?php endif;
        endwhile; ?>
        </dl>
        <?php
        wp_reset_postdata();
    endif;
}

/**
 * ==CALENDAR SHORTCODE
 * This creates a shortcode that can be placed into any page content area
 * The format is [smartcalendar month="month-value" year="year-value"]
 * ============================================================================================================= */
add_shortcode( 'smartcalendar', 'calendartag_func' );
function calendartag_func( $atts ) {
    $timestamp = current_time( 'timestamp' );
    $a = shortcode_atts( array(
        'month' => date('m', $timestamp),
        'year' => date('Y', $timestamp),
    ), $atts );

    sd_wpcalendar($date = "{$a['year']}-{$a['month']}");
}

/**
 * ==CALENDAR
 * This writes the calendar to the page
 * ============================================================================================================= */
function sd_wpcalendar($date = null, $args = array()) {

    $defaults = array(
        'name' => 'F Y',
        'id' => '',
        'class' => '',
        'before_title' => '<h1>',
        'after_title' => '</h1>',
        'before_day' => '',
        'after_day' => '',
        'before_date' => '',
        'after_date' => '',
    );

    $args = wp_parse_args( $args, $defaults );

    //get date
    if (is_null($date)) :
        $timestamp = current_time( 'timestamp' );
    else:
        $timestamp = strtotime($date, current_time( 'timestamp' ) );
    endif;

    //separate the day, month, and year in separate variables
    $day = date('d', $timestamp);
    $month = date('m', $timestamp);
    $year = date('Y', $timestamp);

    //get the title
    $title = date($args['name'], $timestamp);

    //get the first day of the month
    $first_day = mktime(0,0,0,$month, 1, $year);

    //get the last day of the month
    $last_day = date('t', $timestamp);

    //get name of the first day of the month
    $day_of_week = date('D', $first_day);

    //Once we know what day of the week it falls on, we know how many blank days occure before it. If the first day of the week is a Sunday then it would be zero
    switch($day_of_week){
        case "Sun": $blank = 0; break;
        case "Mon": $blank = 1; break;
        case "Tue": $blank = 2; break;
        case "Wed": $blank = 3; break;
        case "Thu": $blank = 4; break;
        case "Fri": $blank = 5; break;
        case "Sat": $blank = 6; break;
    }

    //We then determine how many days are in the current month
    $days_in_month = cal_days_in_month(0, $month, $year);

    //Get array of holidays to be displayed in the calendar
    $holidays = create_holidays_list($year);

    //Get array of all post types
    $the_post_types = get_post_types( '', 'names' );

    //Add custom fields to all post types except 'calendar-entry'
    $calendar_custom_field_array = array();
    foreach ( $the_post_types as $the_post_type ) :
        if ( get_calendar_setting( $the_post_type ) == 'yes' ) :
            array_push($calendar_custom_field_array, $the_post_type );
        endif;
    endforeach;
    $calendar_custom_field_query = new WP_Query( array(
        'post_type' => $calendar_custom_field_array,
        'post_status' => array(
            'publish', 'future'
            ),
        'posts_per_page' => -1,
    ) );
    if( $calendar_custom_field_query->have_posts() ):
        while( $calendar_custom_field_query->have_posts() ): $calendar_custom_field_query->the_post();
            $post_type = get_post_type( get_the_ID() );
            if ( $post_type == 'calendar-entry' ) {
                add_post_meta(get_the_ID(), 'Calendar Note', '', true);
            } else {
                add_post_meta(get_the_ID(), 'Calendar Note', '', true);
                add_post_meta(get_the_ID(), 'Calendar Date (alternate)', '', true);
                add_post_meta(get_the_ID(), 'Calendar Note (alternate)', '', true);
            }
            //delete_post_meta(get_the_ID(), 'Calendar Text', '');
        endwhile;
        wp_reset_postdata();
    endif;
    ?>

    <div class="calendar-grid">
        <?php echo $args['before_title'].$title.$args['after_title']; ?>
        <div class="calendar-row">
            <div class="calendar-header">
                <div class="calendar-day weekend"><div class="day-content">Sunday</div></div>
                <div class="calendar-day"><div class="day-content">Monday</div></div>
                <div class="calendar-day"><div class="day-content">Tuesday</div></div>
                <div class="calendar-day"><div class="day-content">Wednesday</div></div>
                <div class="calendar-day"><div class="day-content">Thursday</div></div>
                <div class="calendar-day"><div class="day-content">Friday</div></div>
                <div class="calendar-day weekend"><div class="day-content">Saturday</div></div>
            </div>
        </div>
        <div class="calendar-row">
            <div class="calendar-body">
            <?php
            //This counts the days in the week, up to 7
            $day_count = 1;
            //first we take care of those blank days
            while ( $blank > 0 ) :
                $classes = 'calendar-day prev-month';
                if ( $day_count == 1) :
                    $classes .= ' weekend';
                endif;
                ?>
                <div class="<?php echo $classes; ?>"><div class="day-content">&nbsp;</div></div>
                <?php
                $blank = $blank - 1;
                $day_count++;
            endwhile;

            //sets the first day of the month to 1
            $day_num = 1;

            //count up the days, until we've done all of them in the month
            while ( $day_num <= $days_in_month ) :
                $classes = null;
                $day_name = null;
                $currentTimestamp = mktime(0,0,0,$month, $day_num, $year);
                $currentDate = date('Ymd', $currentTimestamp);
                $currentDay = date('l', $currentTimestamp);
                foreach($holidays as $date => $holiday) :
                    if ($currentDate == date('Ymd', strtotime($date))) :
                        $classes .= ' holiday';
                        $day_name = $holiday;
                    endif;
                endforeach;
                if ($currentDate == date('Ymd', TODAY)) :
                        $classes .= ' today';
                endif;
                if ($currentDay == 'Sunday' OR $currentDay == 'Saturday') :
                        $classes .= ' weekend';
                endif; ?>
                    <div class="calendar-day<?php echo $classes; ?>">
                        <div class="day-content">
                            <time datetime="<?php echo date('Y-m-d', $currentTimestamp); ?>"><?php echo $day_num; ?><ins><?php echo $currentDay; ?></ins></time>
                            <?php if ($day_name) : ?>
                            <span class="day-name"><?php echo $day_name; ?></span>
                            <?php endif;
                            //Pull calendar entries from the 'calendar-entry' post type
                            get_calendar_entries( $currentDate, $the_post_types );

                            //Pull calendar entries from any post type that is set to post on the publish date
                            get_publish_date_entries( $currentDate, $the_post_types );

                            //Pull calendar entries from any post type that has an alternate calendar date set
                            get_alternate_date_entries( $currentDate, $the_post_types );

                            ?>
                        </div>
                    </div>
                <?php
                $day_num++;
                $day_count++;

                //Make sure we start a new row every week
                if ($day_count > 7) :
                    $day_count = 1;
                endif;
            endwhile;

            //Finally we finish out the table with some blank details if needed
            while ( $day_count > 1 && $day_count <= 7 ) :
                $classes = 'calendar-day next-month';
                if ( $day_count == 7) :
                    $classes .= ' weekend';
                endif;
                ?>
                <div class="<?php echo $classes; ?>"><div class="day-content">&nbsp;</div></div>
                <?php
                $day_count++;
            endwhile; ?>
            </div>
        </div>
    </div>
    <?php
}

add_action( 'admin_menu', 'calendar_settings_page_init' );
function calendar_settings_page_init() {
    $theme_data = wp_get_theme();
    //$settings_page = add_theme_page( 'Calendar Settings', 'Calendar Settings', 'edit_theme_options', 'calendar-settings', 'calendar_settings_page' );
    $settings_page = add_options_page( 'Calendar Settings', 'Calendar Settings', 'edit_theme_options', 'calendar-settings', 'calendar_settings_page' );
    add_action( "load-{$settings_page}", 'load_calendar_settings_page' );
}

add_action( 'admin_init', 'calendar_admin_init' );
function calendar_admin_init() {
    //name of group, name of table row, sanitizing callback
    register_setting( 'calendar_options_group', 'calendar_options' );
}

function load_calendar_settings_page() {
    if ( isset( $_POST["calendar-settings-submit"] ) AND $_POST["calendar-settings-submit"] == 'Y' ) {
        check_admin_referer( "calendar-settings-page" );
        save_calendar_settings();
        $url_parameters = isset($_GET['tab'])? 'updated=true&tab='.$_GET['tab'] : 'updated=true';
        wp_redirect(admin_url('options-general.php?page=calendar-settings&'.$url_parameters));
        exit;
    }
}

function save_calendar_settings() {
    global $pagenow;
    $input = get_option( 'calendar_options' );
    $the_post_types = get_post_types( '', 'names' );

    if ( $pagenow == 'options-general.php' ){
        if ( isset ( $_GET['tab'] ) )
            $tab = $_GET['tab'];
        else
            $tab = 'basic';

        switch ( $tab ){
            case 'basic' :
                //AVAILABLE POST TYPES
                foreach ( $the_post_types as $the_post_type ) :
                    if ( $post_type != 'nav_menu_item' ) :
                    $input[ $the_post_type ] = wp_filter_nohtml_kses( $input[ $the_post_type ] );
                    endif;
                endforeach;
            break;
            case 'holidays' :
                //NATIONAL
                $input[ 'new_year' ] = wp_filter_nohtml_kses( $input[ 'new_year' ] );
                $input[ 'martin_luther_king' ] = wp_filter_nohtml_kses( $input[ 'martin_luther_king' ] );
                $input[ 'groundhog' ] = wp_filter_nohtml_kses( $input[ 'groundhog' ] );
                $input[ 'lincoln' ] = wp_filter_nohtml_kses( $input[ 'lincoln' ] );
                $input[ 'valentines' ] = wp_filter_nohtml_kses( $input[ 'valentines' ] );
                $input[ 'presidents' ] = wp_filter_nohtml_kses( $input[ 'presidents' ] );
                $input[ 'st_patricks' ] = wp_filter_nohtml_kses( $input[ 'st_patricks' ] );
                $input[ 'easter' ] = wp_filter_nohtml_kses( $input[ 'easter' ] );
                $input[ 'april_fools' ] = wp_filter_nohtml_kses( $input[ 'april_fools' ] );
                $input[ 'earth' ] = wp_filter_nohtml_kses( $input[ 'earth' ] );
                $input[ 'mothers' ] = wp_filter_nohtml_kses( $input[ 'mothers' ] );
                $input[ 'memorial' ] = wp_filter_nohtml_kses( $input[ 'memorial' ] );
                $input[ 'flag' ] = wp_filter_nohtml_kses( $input[ 'flag' ] );
                $input[ 'fathers' ] = wp_filter_nohtml_kses( $input[ 'fathers' ] );
                $input[ 'independence' ] = wp_filter_nohtml_kses( $input[ 'independence' ] );
                $input[ 'patriot' ] = wp_filter_nohtml_kses( $input[ 'patriot' ] );
                $input[ 'labor' ] = wp_filter_nohtml_kses( $input[ 'labor' ] );
                $input[ 'bosses' ] = wp_filter_nohtml_kses( $input[ 'bosses' ] );
                $input[ 'halloween' ] = wp_filter_nohtml_kses( $input[ 'halloween' ] );
                $input[ 'columbus' ] = wp_filter_nohtml_kses( $input[ 'columbus' ] );
                $input[ 'all_saints' ] = wp_filter_nohtml_kses( $input[ 'all_saints' ] );
                $input[ 'veterans' ] = wp_filter_nohtml_kses( $input[ 'veterans' ] );
                $input[ 'thanksgiving' ] = wp_filter_nohtml_kses( $input[ 'thanksgiving' ] );
                $input[ 'christmas_eve' ] = wp_filter_nohtml_kses( $input[ 'christmas_eve' ] );
                $input[ 'christmas' ] = wp_filter_nohtml_kses( $input[ 'christmas' ] );
                $input[ 'kwanzaa' ] = wp_filter_nohtml_kses( $input[ 'kwanzaa' ] );
                $input[ 'new_year_eve' ] = wp_filter_nohtml_kses( $input[ 'new_year_eve' ] );
            break;
        }
    }

    $updated = update_option( "calendar_options", $input );
}

function calendar_settings_tabs( $current = 'basic' ) {
    $tabs = array( 'basic' => 'Basic', 'holidays' => 'Holidays' );
    $links = array();
    echo '<div id="icon-options-general" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=calendar-settings&tab=$tab'>$name</a>";

    }
    echo '</h2>';
}

/**
 * ==GET CALENDAR SETTING
 * get calendar settings values
 * ============================================================================================================= */
function get_calendar_setting( $token ) {
    $constant = get_option( 'calendar_options' );
    return $constant[ $token ];
}

function calendar_settings_page() {
    if( !current_user_can( 'manage_options' ) ):
        wp_die( 'Access Denied' );
    else:
        global $pagenow;
        $page_query = new WP_Query( array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ) );
        $settings = get_option( 'calendar_options' );
        $theme_data = wp_get_theme();
        $plugin_data = get_plugin_data( plugin_dir_url( __FILE__ ) . 'sd-wpcalendar.php' );
        $the_post_types = get_post_types( '', 'names' );
        ?>

        <div class="wrap">
            <h2><?php echo $plugin_data['Name']; ?> Settings</h2>

            <?php
                if ( isset( $_GET['updated'] ) AND 'true' == esc_attr( $_GET['updated'] ) ) echo '<div class="updated" ><p>Calendar Settings updated.</p></div>';

                if ( isset ( $_GET['tab'] ) ) calendar_settings_tabs($_GET['tab']); else calendar_settings_tabs('basic');
            ?>

            <div class="wrap">
                <form method="post" action="options.php">
                    <?php
                    wp_nonce_field( "calendar-settings-page" );
                    //connect this form to the settings group we registered in the plugin
                    settings_fields( 'calendar_options_group' );

                    if ( $pagenow == 'options-general.php' ){

                        if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
                        else $tab = 'basic';

                        switch ( $tab ){
                            case 'basic' :
                                ?>
                                <input type="hidden" name="calendar_options[new_year]" id="new_year" class="regular-text code" value="<?php if( isset( $settings['new_year'] ) ): echo $settings['new_year']; endif; ?>">
                                <input type="hidden" name="calendar_options[martin_luther_king]" id="martin_luther_king" class="regular-text code" value="<?php if( isset( $settings['martin_luther_king'] ) ): echo $settings['martin_luther_king']; endif; ?>">
                                <input type="hidden" name="calendar_options[groundhog]" id="groundhog" class="regular-text code" value="<?php if( isset( $settings['groundhog'] ) ): echo $settings['groundhog']; endif; ?>">
                                <input type="hidden" name="calendar_options[lincoln]" id="lincoln" class="regular-text code" value="<?php if( isset( $settings['lincoln'] ) ): echo $settings['lincoln']; endif; ?>">
                                <input type="hidden" name="calendar_options[valentines]" id="valentines" class="regular-text code" value="<?php if( isset( $settings['valentines'] ) ): echo $settings['valentines']; endif; ?>">
                                <input type="hidden" name="calendar_options[presidents]" id="presidents" class="regular-text code" value="<?php if( isset( $settings['presidents'] ) ): echo $settings['presidents']; endif; ?>">
                                <input type="hidden" name="calendar_options[st_patricks]" id="st_patricks" class="regular-text code" value="<?php if( isset( $settings['st_patricks'] ) ): echo $settings['st_patricks']; endif; ?>">
                                <input type="hidden" name="calendar_options[easter]" id="easter" class="regular-text code" value="<?php if( isset( $settings['easter'] ) ): echo $settings['easter']; endif; ?>">
                                <input type="hidden" name="calendar_options[april_fools]" id="april_fools" class="regular-text code" value="<?php if( isset( $settings['april_fools'] ) ): echo $settings['april_fools']; endif; ?>">
                                <input type="hidden" name="calendar_options[earth]" id="earth" class="regular-text code" value="<?php if( isset( $settings['earth'] ) ): echo $settings['earth']; endif; ?>">
                                <input type="hidden" name="calendar_options[mothers]" id="mothers" class="regular-text code" value="<?php if( isset( $settings['mothers'] ) ): echo $settings['mothers']; endif; ?>">
                                <input type="hidden" name="calendar_options[memorial]" id="memorial" class="regular-text code" value="<?php if( isset( $settings['memorial'] ) ): echo $settings['memorial']; endif; ?>">
                                <input type="hidden" name="calendar_options[flag]" id="flag" class="regular-text code" value="<?php if( isset( $settings['flag'] ) ): echo $settings['flag']; endif; ?>">
                                <input type="hidden" name="calendar_options[fathers]" id="fathers" class="regular-text code" value="<?php if( isset( $settings['fathers'] ) ): echo $settings['fathers']; endif; ?>">
                                <input type="hidden" name="calendar_options[independence]" id="independence" class="regular-text code" value="<?php if( isset( $settings['independence'] ) ): echo $settings['independence']; endif; ?>">
                                <input type="hidden" name="calendar_options[patriot]" id="patriot" class="regular-text code" value="<?php if( isset( $settings['patriot'] ) ): echo $settings['patriot']; endif; ?>">
                                <input type="hidden" name="calendar_options[labor]" id="labor" class="regular-text code" value="<?php if( isset( $settings['labor'] ) ): echo $settings['labor']; endif; ?>">
                                <input type="hidden" name="calendar_options[bosses]" id="bosses" class="regular-text code" value="<?php if( isset( $settings['bosses'] ) ): echo $settings['bosses']; endif; ?>">
                                <input type="hidden" name="calendar_options[halloween]" id="halloween" class="regular-text code" value="<?php if( isset( $settings['halloween'] ) ): echo $settings['halloween']; endif; ?>">
                                <input type="hidden" name="calendar_options[columbus]" id="columbus" class="regular-text code" value="<?php if( isset( $settings['columbus'] ) ): echo $settings['columbus']; endif; ?>">
                                <input type="hidden" name="calendar_options[all_saints]" id="all_saints" class="regular-text code" value="<?php if( isset( $settings['all_saints'] ) ): echo $settings['all_saints']; endif; ?>">
                                <input type="hidden" name="calendar_options[veterans]" id="veterans" class="regular-text code" value="<?php if( isset( $settings['veterans'] ) ): echo $settings['veterans']; endif; ?>">
                                <input type="hidden" name="calendar_options[thanksgiving]" id="thanksgiving" class="regular-text code" value="<?php if( isset( $settings['thanksgiving'] ) ): echo $settings['thanksgiving']; endif; ?>">
                                <input type="hidden" name="calendar_options[christmas_eve]" id="christmas_eve" class="regular-text code" value="<?php if( isset( $settings['christmas_eve'] ) ): echo $settings['christmas_eve']; endif; ?>">
                                <input type="hidden" name="calendar_options[christmas]" id="christmas" class="regular-text code" value="<?php if( isset( $settings['christmas'] ) ): echo $settings['christmas']; endif; ?>">
                                <input type="hidden" name="calendar_options[kwanzaa]" id="kwanzaa" class="regular-text code" value="<?php if( isset( $settings['kwanzaa'] ) ): echo $settings['kwanzaa']; endif; ?>">
                                <input type="hidden" name="calendar_options[new_year_eve]" id="new_year_eve" class="regular-text code" value="<?php if( isset( $settings['new_year_eve'] ) ): echo $settings['new_year_eve']; endif; ?>">
                                <h3 class="title">Getting Started</h3>
                                <p></p>
                                <table class="form-table">
                                    <tbody>
                                        <tr valign="top">
                                            <th scope="row">Available Post Types</th>
                                            <td>
                                                <fieldset>
                                                    <?php
                                                    foreach ( $the_post_types as $the_post_type ) :
                                                        if ( $the_post_type != 'calendar-entry' AND $the_post_type != 'nav_menu_item' AND $the_post_type != 'revision' ) : ?>
                                                        <legend class="screen-reader-text"><span><?php echo $the_post_type; ?></span></legend>
                                                        <label for="<?php echo $the_post_type; ?>">
                                                            <input name="calendar_options[<?php echo $the_post_type; ?>]" type="checkbox" id="<?php echo $the_post_type; ?>" value="yes" <?php checked( 'yes', $settings[ $the_post_type ] ); ?>>
                                                            <?php echo $the_post_type; ?>
                                                        </label>
                                                        <br>
                                                        <?php
                                                        elseif ( $the_post_type == 'revision' OR $the_post_type == 'calendar-entry' ) : ?>
                                                            <input name="calendar_options[<?php echo $the_post_type; ?>]" type="hidden" id="<?php echo $the_post_type; ?>" value="yes">
                                                        <?php
                                                        endif;
                                                    endforeach;
                                                    ?>
                                                    <p class="description">(Check the post types that you would like to make available for calendar posts.)</p>
                                                </fieldset>
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row">Available Pages</th>
                                            <td>
                                                <fieldset>
                                                    <?php
                                                    if( have_posts() ): while( have_posts() ): the_post();
                                                        if( $page_query->have_posts() ):
                                                            while( $page_query->have_posts() ): $page_query->the_post(); ?>
                                                                <legend class="screen-reader-text"><span><?php the_title(); ?></span></legend>
                                                                <label for="<?php the_title(); ?>">
                                                                    <input name="calendar_options[<?php the_title(); ?>]" type="checkbox" id="<?php the_title(); ?>" value="yes" <?php checked( 'yes', $settings[ the_title() ] ); ?>>
                                                                    <?php the_title(); ?>
                                                                </label>
                                                                <br>
                                                            <?php
                                                            endwhile;
                                                            wp_reset_postdata();
                                                        endif;
                                                    endwhile; endif; wp_reset_postdata();
                                                    ?>
                                                </fieldset>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php
                            break;
                            case 'holidays' :
                                foreach ( $the_post_types as $the_post_type ) :
                                    if ( $the_post_type != 'nav_menu_item' ) : ?>
                                    <input type="hidden" name="calendar_options[<?php echo $the_post_type; ?>]" id="<?php echo $the_post_type; ?>" class="regular-text code" value="<?php if( isset( $settings[ $the_post_type ] ) ): echo $settings[ $the_post_type ]; endif; ?>">
                                    <?php
                                    endif;
                                endforeach;
                                ?>
                                <h3 class="title">Holiday Settings</h3>
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">National Holidays</th>
                                            <td>
                                                <fieldset>
                                                    <legend class="screen-reader-text"><span>National Holidays</span></legend>
                                                    <label for="new_year">
                                                        <input name="calendar_options[new_year]" type="checkbox" id="new_year" value="yes" <?php checked( 'yes', $settings['new_year'] ); ?>>
                                                        New Year's Day
                                                    </label>
                                                    <br>
                                                    <label for="martin_luther_king">
                                                        <input name="calendar_options[martin_luther_king]" type="checkbox" id="martin_luther_king" value="yes" <?php checked( 'yes', $settings['martin_luther_king'] ); ?>>
                                                        Martin Luther King Jr. Day
                                                    </label>
                                                    <br>
                                                    <label for="groundhog">
                                                        <input name="calendar_options[groundhog]" type="checkbox" id="groundhog" value="yes" <?php checked( 'yes', $settings['groundhog'] ); ?>>
                                                        Groundhog Day
                                                    </label>
                                                    <br>
                                                    <label for="lincoln">
                                                        <input name="calendar_options[lincoln]" type="checkbox" id="lincoln" value="yes" <?php checked( 'yes', $settings['lincoln'] ); ?>>
                                                        Lincoln's Birthday
                                                    </label>
                                                    <br>
                                                    <label for="valentines">
                                                        <input name="calendar_options[valentines]" type="checkbox" id="valentines" value="yes" <?php checked( 'yes', $settings['valentines'] ); ?>>
                                                        Valentine's Day
                                                    </label>
                                                    <br>
                                                    <label for="presidents">
                                                        <input name="calendar_options[presidents]" type="checkbox" id="presidents" value="yes" <?php checked( 'yes', $settings['presidents'] ); ?>>
                                                        President's Day
                                                    </label>
                                                    <br>
                                                    <label for="st_patricks">
                                                        <input name="calendar_options[st_patricks]" type="checkbox" id="st_patricks" value="yes" <?php checked( 'yes', $settings['st_patricks'] ); ?>>
                                                        St. Patrick's Day
                                                    </label>
                                                    <br>
                                                    <label for="easter">
                                                        <input name="calendar_options[easter]" type="checkbox" id="easter" value="yes" <?php checked( 'yes', $settings['easter'] ); ?>>
                                                        Easter
                                                    </label>
                                                    <br>
                                                    <label for="april_fools">
                                                        <input name="calendar_options[april_fools]" type="checkbox" id="april_fools" value="yes" <?php checked( 'yes', $settings['april_fools'] ); ?>>
                                                        April Fool's Day
                                                    </label>
                                                    <br>
                                                    <label for="earth">
                                                        <input name="calendar_options[earth]" type="checkbox" id="earth" value="yes" <?php checked( 'yes', $settings['earth'] ); ?>>
                                                        Earth Day
                                                    </label>
                                                    <br>
                                                    <label for="mothers">
                                                        <input name="calendar_options[mothers]" type="checkbox" id="mothers" value="yes" <?php checked( 'yes', $settings['mothers'] ); ?>>
                                                        Mother's Day
                                                    </label>
                                                    <br>
                                                    <label for="memorial">
                                                        <input name="calendar_options[memorial]" type="checkbox" id="memorial" value="yes" <?php checked( 'yes', $settings['memorial'] ); ?>>
                                                        Memorial Day
                                                    </label>
                                                    <br>
                                                    <label for="flag">
                                                        <input name="calendar_options[flag]" type="checkbox" id="flag" value="yes" <?php checked( 'yes', $settings['flag'] ); ?>>
                                                        Flag Day
                                                    </label>
                                                    <br>
                                                    <label for="fathers">
                                                        <input name="calendar_options[fathers]" type="checkbox" id="fathers" value="yes" <?php checked( 'yes', $settings['fathers'] ); ?>>
                                                        Father's Day
                                                    </label>
                                                    <br>
                                                    <label for="independence">
                                                        <input name="calendar_options[independence]" type="checkbox" id="independence" value="yes" <?php checked( 'yes', $settings['independence'] ); ?>>
                                                        Independence Day
                                                    </label>
                                                    <br>
                                                    <label for="patriot">
                                                        <input name="calendar_options[patriot]" type="checkbox" id="patriot" value="yes" <?php checked( 'yes', $settings['patriot'] ); ?>>
                                                        Patriot Day
                                                    </label>
                                                    <br>
                                                    <label for="labor">
                                                        <input name="calendar_options[labor]" type="checkbox" id="labor" value="yes" <?php checked( 'yes', $settings['labor'] ); ?>>
                                                        Labor Day
                                                    </label>
                                                    <br>
                                                    <label for="bosses">
                                                        <input name="calendar_options[bosses]" type="checkbox" id="bosses" value="yes" <?php checked( 'yes', $settings['bosses'] ); ?>>
                                                        Bosses' Day
                                                    </label>
                                                    <br>
                                                    <label for="halloween">
                                                        <input name="calendar_options[halloween]" type="checkbox" id="halloween" value="yes" <?php checked( 'yes', $settings['halloween'] ); ?>>
                                                        Halloween
                                                    </label>
                                                    <br>
                                                    <label for="columbus">
                                                        <input name="calendar_options[columbus]" type="checkbox" id="columbus" value="yes" <?php checked( 'yes', $settings['columbus'] ); ?>>
                                                        Columbus Day
                                                    </label>
                                                    <br>
                                                    <label for="all_saints">
                                                        <input name="calendar_options[all_saints]" type="checkbox" id="all_saints" value="yes" <?php checked( 'yes', $settings['all_saints'] ); ?>>
                                                        All Saints' Day
                                                    </label>
                                                    <br>
                                                    <label for="veterans">
                                                        <input name="calendar_options[veterans]" type="checkbox" id="veterans" value="yes" <?php checked( 'yes', $settings['veterans'] ); ?>>
                                                        Veterans Day
                                                    </label>
                                                    <br>
                                                    <label for="thanksgiving">
                                                        <input name="calendar_options[thanksgiving]" type="checkbox" id="thanksgiving" value="yes" <?php checked( 'yes', $settings['thanksgiving'] ); ?>>
                                                        Thanksgiving Day
                                                    </label>
                                                    <br>
                                                    <label for="christmas_eve">
                                                        <input name="calendar_options[christmas_eve]" type="checkbox" id="christmas_eve" value="yes" <?php checked( 'yes', $settings['christmas_eve'] ); ?>>
                                                        Christmas Eve
                                                    </label>
                                                    <br>
                                                    <label for="christmas">
                                                        <input name="calendar_options[christmas]" type="checkbox" id="christmas" value="yes" <?php checked( 'yes', $settings['christmas'] ); ?>>
                                                        Christmas
                                                    </label>
                                                    <br>
                                                    <label for="kwanzaa">
                                                        <input name="calendar_options[kwanzaa]" type="checkbox" id="kwanzaa" value="yes" <?php checked( 'yes', $settings['kwanzaa'] ); ?>>
                                                        Kwanzaa
                                                    </label>
                                                    <br>
                                                    <label for="new_year_eve">
                                                        <input name="calendar_options[new_year_eve]" type="checkbox" id="new_year_eve" value="yes" <?php checked( 'yes', $settings['new_year_eve'] ); ?>>
                                                        New Year's Eve
                                                    </label>
                                                    <br>
                                                    <p class="description">(Check the National Holidays that you wish to display in the calendar.)</p>
                                                </fieldset>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php
                            break;
                        }
                    }
                    ?>
                    <p class="submit" style="clear: both;">
                        <input type="submit" name="Submit"  class="button-primary" value="Update Settings" />
                        <input type="hidden" name="calendar-settings-submit" value="Y" />
                    </p>
                </form>

                <p><?php echo $theme_data['Name'] ?> by <a href="http://www.sernadesigns.com/">SernaDesigns.com</a> | <a href="http://twitter.com/sernadesigns">Follow me on Twitter</a>! | Join <a href="http://on.fb.me/GBQNbQ">SernaDesigns on Facebook</a>!</p>
            </div>

        </div>
    <?php
    endif;
}

//Don't close PHP
