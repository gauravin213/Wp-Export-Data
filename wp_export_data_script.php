<?php

/*
* Export buttom action
*/
function cat_str_shortcode_fun(){
ob_start();

    global $wpdb;

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $args = array(
        'posts_per_page'   => 1000,
        'paged'            => $paged,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'product',
        //'post_status'      => 'publish',
        /*'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => array(44),
                'operator' => 'IN'
            )
        )*/
    );


    $query = new WP_Query( $args );

    $count = $query->found_posts; 

    echo 'Total Product count: '.$count; echo "<br>";

    if($query->have_posts()){

        while( $query->have_posts() ) {  $query->the_post();

            $ID = get_the_ID();

            $title = get_the_title(); 

            $title = get_the_title($ID);

            $link = get_permalink($ID);

            $post_id = $ID;

            $category = str_get_category($post_id);

            $compatibility_chart = str_get_compatibility_year($post_id);

            /*$p[] = array(
                $post_id,
                $title,
                $link,
                $category,
                $compatibility_chart
            );*/


            $p[] =  array(
                'ID'    => $post_id,
                'Product name'  =>$title,
                'Product link' =>$link, 
                'Category'  =>$category,
                'Compatibility chart year'  =>$compatibility_chart
            );

        }


        echo "<pre>"; print_r($p); echo "</pre>";

        $GLOBALS['wp_query']->max_num_pages = $query->max_num_pages;
        the_posts_pagination( array(
        'mid_size' => 3,
        'prev_text' => __( '<', 'welsh-womens-aid' ),
        'next_text' => __( '>', 'welsh-womens-aid' ),
        ) );

        //wp_reset_postdata();

    }else{
        echo  'no post';
    }

?>
<div>
     <div>
        <a class="button-primary"  href="<?php echo admin_url( 'admin-post.php?action=print.xls&paged='.$paged ); ?>">Export xls</a>
    </div>
    <div>
        <a class="button-primary"  href="<?php echo admin_url( 'admin-post.php?action=print.csv' ); ?>">Export csv</a>
    </div>
</div>
   
<?php
return ob_get_clean();
}
add_shortcode('cat_str_shortcode', 'cat_str_shortcode_fun');



/*
* Get category
*/
function str_get_category($product_id){
    $terms = get_the_terms($product_id , 'product_cat');
    $terms = array_column($terms, 'name');
    $category_i = implode(" | ", $terms); 
    return $category_i;
}


/*
* Get year from compability chart
*/
function str_get_compatibility_year($product_id){
    $compat_list   = get_post_meta( $product_id, '_ebay_item_compatibility_list', true );
    $compat_list = array_column($compat_list, 'applications');
    $compat_list2 = array_column($compat_list, 'Year');
    $compat_list3 = array_column($compat_list2, 'value');
    $compat_list3 = array_unique($compat_list3);
    $compatibility_chart_i = implode(" | ", $compat_list3); 
    return $compatibility_chart_i;
}


/*
* Prepaire data before export
*/
function get_csv_formated_data(){

    global $wpdb;

    /*$q = "
        SELECT ID FROM {$wpdb->prefix}posts WHERE 
        (ID BETWEEN 28188 AND 75940) 
        AND 
        (post_type ='product' AND post_status ='publish' )
    ";
    
    $result = $wpdb->get_results($q, ARRAY_A);

    $result = array_column($result, 'ID');*/

    //
    /*$cat_id = 44;
    $term_children = get_term_children( $cat_id, 'product_cat' ); 
    $categories = array_merge(array($cat_id), $term_children);
    $categories_i = implode(', ', $categories);
    $q = "SELECT ID FROM wp_posts WHERE ID IN (SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN (SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE taxonomy='product_cat' AND term_id IN (".$categories_i.") )) AND post_type='product' AND post_status='publish'";
    $result = $wpdb->get_results($q, ARRAY_A);
    $result = array_column($result, 'ID');*/
    //

    /*foreach ($result as $ID) {

        $title = get_the_title($ID);

        $link = get_permalink($ID);

        $post_id = $ID;

        $category = str_get_category($post_id);

        $compatibility_chart = str_get_compatibility_year($post_id);

        $p[] = array(
            $post_id,
            $title,
            $link,
            $category,
            $compatibility_chart
        );

    }*/





    //
    $paged = ($_GET['paged']) ? $_GET['paged'] : 1;

    $args = array(
        'posts_per_page'   => 1000,
        'paged'            => $paged,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'product',
        'post_status'      => 'publish',
    );


    $query = new WP_Query( $args );

    if($query->have_posts()){

        while( $query->have_posts() ) {  $query->the_post();

            $ID = get_the_ID();
            
            $title = get_the_title();

            $title = get_the_title($ID);

            $link = get_permalink($ID);

            $post_id = $ID;

            $category = str_get_category($post_id);

            $compatibility_chart = str_get_compatibility_year($post_id);

            $p[] = array(
                $post_id,
                $title,
                $link,
                $category,
                $compatibility_chart
            );

        }

        //wp_reset_postdata();

    }else{
        echo  'no post';
        die();
    }
    //

    return $p;
}


/*
* Expport data xls formate
*/ 
add_action( 'admin_post_print.xls', 'print_xls' );
add_action( 'admin_post_nopriv_print.xls', 'print_xls' );
function print_xls(){
    export_data('xls');
}


/*
* Expport data csv formate
*/ 
add_action( 'admin_post_print.csv', 'print_csv' );
add_action( 'admin_post_nopriv_print.csv', 'print_csv' );
function print_csv(){
    export_data('csv');
}


/*
* Expport data function
*/ 
function export_data($file_extension){

    if ( ! current_user_can( 'manage_options' ) )
        return;

    $header_row = array(
        'ID', 
        'Product name',
        'Product link', 
        'Category',
        'Compatibility chart year'
    );

    $data_rows = get_csv_formated_data();

    $datetime = date("Y-m-d h:i");

    $paged = ($_GET['paged']) ? $_GET['paged'] : 1;

    $filename = 'product-page-'.$paged.'-'.$datetime.'.'.$file_extension;
        
    $fh = @fopen( 'php://output', 'w' );
    fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header( 'Content-Description: File Transfer' );
    header( 'Content-type: text/'.$file_extension );
    header( "Content-Disposition: attachment; filename={$filename}" );
    header( 'Expires: 0' );
    header( 'Pragma: public' );
    fputcsv( $fh, $header_row );
    foreach ( $data_rows as $data_row ) {
        fputcsv( $fh, $data_row );
    }
    fclose( $fh );
    die();

}