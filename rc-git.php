<?php
/**
 * Plugin Name: Recipes Carousel 
 */
    
function recipe_selection_shortcode( $atts ) {
    $attributes = shortcode_atts( array(
        'count' => 8,
        'tag' => '',
        'category' => '', 
        'total_time' => '',
        'calorie' => '',
        'difficulty' => '', 
        'cooking_method' => '', 
        'cuisine' => '', 
        'search_term' => '', 
        'max_cols' => 3, 
        ), $atts); 
        
    $tax_query = array('relation' => 'AND');
    
    if($attributes['tag'] != ''){
        $tax_query[] = array(
            'taxonomy' => 'cp_recipe_tags',
            'field'    => 'slug',
            'terms'    => $attributes['tag']
        );
    }
    
    if($attributes['category'] != ''){
        $tax_query[] = array(
            'taxonomy' => 'cp_recipe_category',
            'field'    => 'slug',
            'terms'    => $attributes['category']
        );
    }
    
    if($attributes['total_time'] != ''){
        $tax_query[] = array(
            'taxonomy' => 'cooking_time',
            'field'    => 'slug',
            'terms'    => $attributes['total_time']
        );
    }
    
    if($attributes['calorie'] != ''){
        $tax_query[] = array(
            'taxonomy' => 'calorie',
            'field'    => 'slug',
            'terms'    => $attributes['calorie']
        );
    }
    
    if($attributes['difficulty'] != ''){
        $tax_query[] = array(
            'taxonomy' => 'difficulty',
            'field'    => 'slug',
            'terms'    => $attributes['difficulty']
        );

    }
    
    if($attributes['cooking_method'] != ''){
        $tax_query[] = array(
            'taxonomy' => 'cp_recipe_cooking_method',
            'field'    => 'slug',
            'terms'    => $attributes['cooking_method']
        );
    }
    
    if($attributes['cuisine'] != ''){
        $tax_query[] = array(
            'taxonomy' => 'cp_recipe_cuisine',
            'field'    => 'slug',
            'terms'    => $attributes['cuisine']
        );
    }
        
    $args = array(
        'post_type' => 'cp_recipe',
        'posts_per_page' => $attributes['count'],
        'tax_query' => $tax_query, 
        'orderby' => 'date',
        'order' => 'DESC',
        's' => $attributes['search_term'], 
    );
//echo var_dump($tax_query);
    // Get the posts
    $recipes = get_posts($args);

    $RS_COLS_MAX = 4;
    $RS_COLS_MIN = 1;
    $max_cols = 3; //Default to 3
    $max_cols_attr = $attributes['max_cols'];
    if($RS_COLS_MIN <= $max_cols_attr && $max_cols_attr <= $RS_COLS_MAX)
    {
        $max_cols = $max_cols_attr;
    }
    
    $output = "<div class='recipe-selection custom-recipe-cards-container rs-max-cols-{$max_cols}'>";
  
    foreach ($recipes as $recipe)
    {
        $author_id=$recipe->post_author; 
        $recipe_settings = get_post_meta($recipe->ID, '_recipe_settings', true);
        
        $likes = get_post_meta($recipe->ID, 'likes_count', true); 
        $likes = $likes ? $likes : '0';
        $likes = "<div class='likes'>".$likes."</div>";
        
        $total_time = '';
        if (isset($recipe_settings['total_time']) && $recipe_settings['total_time']) {
    	    $total_time = '<div class="time">'.$recipe_settings['total_time'].' دقیقه</div>';
    	}
        
        $output .= "<a href='".get_post_permalink($recipe->ID)."' class='custom-recipe-card'>".
                        '<div class="cooklett-recipe-img-container">'.
                            get_the_post_thumbnail($recipe->ID, "small").
                            '<div class="overlay">'.
            			        $likes.
            			        $total_time.
    			            '</div>'.
                        '</div>'.
                        "<div class='cooklett-recipe-title-author'>".
                            "<h2>".$recipe->post_title."</h2>".
                        	"<div class='author'>".
                            	"<div class='author-avatar'>".
                            	    "<img src='".get_avatar_url($author_id)."' width='30' height='30' />".
                            	"</div>".
                            	"<div class='author-name'>".get_the_author_meta('display_name', $author_id)."</div>".
                        	"</div>".
                        "</div>".
                    "</a>";
    }
    
    $output .= '</div>'; 
    
    return $output; 
}

add_shortcode( 'recipeselection', 'recipe_selection_shortcode' );


function todays_recipe ($atts){

     $attributes = shortcode_atts( array(
        'col_size' =>1
    ), $atts);
    
    $post_counts = wp_count_posts( $type = 'cp_recipe');
    $offset = (date('Y') + date('m') + date('d')*5 +23548) % $post_counts->publish;

    $args = array(
        'post_type' => 'cp_recipe',
        'offset' => $offset,
        'posts_per_page' => 1
    );

    $recipe = get_posts($args)[0];


    $RS_COLS_MAX = 4;
    $RS_COLS_MIN = 1;
    $max_cols = 3; //Default to 3
    $max_cols_attr = $attributes['col_size'];
    if($RS_COLS_MIN <= $max_cols_attr && $max_cols_attr <= $RS_COLS_MAX)
    {
        $max_cols = $max_cols_attr;
    }
    
    $output = "<div class='recipe-selection custom-recipe-cards-container rs-max-cols-{$max_cols}'>";
    $output .= Make_Output_HTML($recipe, $max_cols);
    $output .= "</div>";

    return $output;
}

add_shortcode( 'todays-recipe', 'todays_recipe' );


function get_recipe_by_Id($atts){
    $attributes = shortcode_atts( array(
        'max_cols' =>3,
        'id' => ''
        ), $atts);

    if($attributes['id'] != ''){
        $recipe = get_post($attributes['id']);

        $RS_COLS_MAX = 4;
        $RS_COLS_MIN = 1;
        $max_cols = 3; //Default to 3
        $max_cols_attr = $attributes['col_size'];
        if($RS_COLS_MIN <= $max_cols_attr && $max_cols_attr <= $RS_COLS_MAX)
        {
            $max_cols = $max_cols_attr;
        }

        $output = "<div class='recipe-selection custom-recipe-cards-container'>";
        $output .= Make_Output_HTML($recipe, $max_cols);
        $output .= "</div>";

        return $output;
    }

    return '';

}

add_shortcode( 'get-recipe', 'get_recipe_by_Id' );



function Make_Output_HTML($recipe){
    // $output = "<div class='recipe-selection custom-recipe-cards-container rs-max-cols-{$max_cols}'>";
    $author_id=$recipe->post_author; 
    $recipe_settings = get_post_meta($recipe->ID, '_recipe_settings', true);
    
    $likes = get_post_meta($recipe->ID, 'likes_count', true); 
    $likes = $likes ? $likes : '0';
    $likes = "<div class='likes'>".$likes."</div>";
    
    $total_time = '';
    if (isset($recipe_settings['total_time']) && $recipe_settings['total_time']) {
        $total_time = '<div class="time">'.$recipe_settings['total_time'].' دقیقه</div>';
    }
    
    $output .= "<a href='".get_post_permalink($recipe->ID)."' class='custom-recipe-card'>".
                    '<div class="cooklett-recipe-img-container">'.
                        get_the_post_thumbnail($recipe->ID, "small").
                        '<div class="overlay">'.
                            $likes.
                            $total_time.
                        '</div>'.
                    '</div>'.
                    "<div class='cooklett-recipe-title-author'>".
                        "<h2>".$recipe->post_title."</h2>".
                        "<div class='author'>".
                            "<div class='author-avatar'>".
                                "<img src='".get_avatar_url($author_id)."' width='30' height='30' />".
                            "</div>".
                            "<div class='author-name'>".get_the_author_meta('display_name', $author_id)."</div>".
                        "</div>".
                    "</div>".
                "</a>";

    return $output;

}

?>
