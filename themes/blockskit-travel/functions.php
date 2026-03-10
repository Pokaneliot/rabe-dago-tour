<?php

/**
 * Blockskit Travel functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Blockskit Travel
 */

define( 'BLOCKSKIT_TRAVEL_URL', trailingslashit( get_stylesheet_directory_uri() ) );

if ( ! function_exists( 'blockskit_travel_setup' ) ) {

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function blockskit_travel_setup() {

		// Make theme available for translation.
		load_theme_textdomain( 'blockskit-travel', get_stylesheet_directory() . '/languages' );
	}
}
add_action( 'after_setup_theme', 'blockskit_travel_setup' );


/**
 * Enqueue scripts and styles
 */
function blockskit_travel_scripts() {
	$version = wp_get_theme( 'blockskit-travel' )->get( 'Version' );
	// enqueue parent style
	wp_enqueue_style('blockskit-travel-parent-style', get_template_directory_uri() . '/style.css');
}
add_action( 'wp_enqueue_scripts', 'blockskit_travel_scripts' );

/**
 * Label update filter.
 */
function blockskit_travel_block_pattern_categories_filter( $block_pattern_categories ){
	$block_pattern_categories['theme']['label'] = __( 'Theme Patterns', 'blockskit-travel' );
	return $block_pattern_categories;
}
add_filter( 'blockskit_base_block_pattern_categories', 'blockskit_travel_block_pattern_categories_filter' );



/**
 * 1. CRÉATION DU CUSTOM POST TYPE
 */
function create_reservation_cpt() {
    $labels = array(
        'name'               => 'Réservations',
        'singular_name'      => 'Réservation',
        'menu_name'          => 'Réservations',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter une réservation',
        'edit_item'          => 'Modifier la réservation',
        'new_item'           => 'Nouvelle réservation',
        'view_item'          => 'Voir la réservation',
        'search_items'       => 'Rechercher des réservations',
        'not_found'          => 'Aucune réservation trouvée',
        'not_found_in_trash' => 'Aucune réservation dans la corbeille',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => false, // Pas besoin de page publique
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'reservation'),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 25,
        'menu_icon'           => 'dashicons-calendar-alt',
        'supports'            => array('title'), // Le titre sera l'ID de réservation
        'show_in_rest'        => true, // Support Gutenberg si besoin
    );

    register_post_type('reservation', $args);
}
add_action('init', 'create_reservation_cpt');



/**
 * 2. CRÉATION DU STATUS DE RÉSERVATION
 */
// Définir les statuts disponibles
function get_reservation_statuses() {
    return array(
        'Pending' => 'Pending',
        'Availability Confirmed' => 'Availability Confirmed',
        'Deposit Requested' => 'Deposit Requested',
        'Deposit Received' => 'Deposit Received',
        'Confirmed' => 'Confirmed',
        'Cancelled' => 'Cancelled'
    );
}


/**
 * 3. MODIFICATION DU STATUS DE RÉSERVATION
 */
// Meta box pour modifier le statut dans l'admin
function reservation_status_meta_box(){
    add_meta_box('reservation_status_box','Reservation Status','reservation_status_box_html','reservation','side','default');
}
add_action('add_meta_boxes','reservation_status_meta_box');

function reservation_status_box_html($post){
    $statuses = get_reservation_statuses();
    $current = get_post_meta($post->ID,'reservation_status',true);
    ?>
    <select name="reservation_status" style="width:100%;">
        <?php foreach($statuses as $key=>$label): ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($current,$key); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}


/**
 * 4. SAUVEGARDE DU STATUS DE RÉSERVATION
 */

// Envoie mail de confirmation au client lorsque le statut passe à "Availability Confirmed" et change automatiquement le statut à "Deposit Requested"
function sendMailAvailabilityConfirmed($post_id, $post) {
    $client_email = get_post_meta($post_id, 'email', true);
    $first_name = get_post_meta($post_id, 'first_name', true);
    $last_name = get_post_meta($post_id, 'last_name', true);
    $travel_date = get_post_meta($post_id, 'travel_date', true);
    $travelers = get_post_meta($post_id, 'travelers', true);
    $deposit_number = '+261XXXXXXXXX'; // numéro où envoyer l’acompte
    
    if ($client_email) {
        $subject = 'Your Reservation is Confirmed! Deposit Requested';
        $body = "Hello $first_name $last_name,\n\n";
        $body .= "Great news! Your reservation for $travelers traveler(s) on $travel_date has been confirmed.\n";
        $body .= "To secure your booking, please send the deposit to the following number: $deposit_number.\n\n";
        $body .= "Once received, we will finalize your reservation and contact you for the next steps.\n\n";
        $body .= "Thank you for booking with us!\n\n";
        $body .= "— Rabe Dago Tour"; // signature
        
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        wp_mail($client_email, $subject, $body, $headers);
    }
}

// Envoie mail de confirmation au client lorsque le statut passe à "Deposit Requested" et change automatiquement le statut à "Confirmed"
function sendMailDepositReceived($post_id, $post) {
    $client_email = get_post_meta($post_id, 'email', true);
    $first_name = get_post_meta($post_id, 'first_name', true);
    $last_name = get_post_meta($post_id, 'last_name', true);
    $travel_date = get_post_meta($post_id, 'travel_date', true);
    $travelers = get_post_meta($post_id, 'travelers', true);

    if ($client_email) {
        $subject = 'Your Reservation is Fully Confirmed!';
        $body = "Hello $first_name $last_name,\n\n";
        $body .= "Good news! We have received your deposit and your reservation for $travelers traveler(s) on $travel_date is now fully confirmed.\n\n";
        $body .= "We look forward to welcoming you on your trip!\n\n";
        $body .= "Thank you for booking with us!\n\n";
        $body .= "— Rabe Dago Tour"; // signature

        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        wp_mail($client_email, $subject, $body, $headers);
    }
}
// Sauvegarde du statut
function save_reservation_status($post_id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['reservation_status'])) {
        $statuses = array_keys(get_reservation_statuses());
        $requested_status = sanitize_text_field($_POST['reservation_status']);
        $old_status = get_post_meta($post_id, 'reservation_status', true);

        if (in_array($requested_status, $statuses)) {

            // --- Workflow spécial pour Availability Confirmed ---
            if ($requested_status === 'Availability Confirmed') { // <-- casse corrigée
                $new_status = 'Deposit Requested'; // changer directement le statut
                update_post_meta($post_id, 'reservation_status', $new_status);

                // --- Envoi email au client ---
                sendMailAvailabilityConfirmed($post_id, $post);
                }else if($requested_status === 'Deposit Received' && $old_status === 'Deposit Requested'){

                $new_status = 'Confirmed'; // changer directement le statut
                update_post_meta($post_id, 'reservation_status', $new_status);
                sendMailDepositReceived($post_id, $post);

            }
             else {
                // Pour tous les autres statuts normaux
                update_post_meta($post_id, 'reservation_status', $requested_status);
            }
        }
    }
}
add_action('save_post_reservation', 'save_reservation_status', 10, 2);

/**
 * 5. CRÉATION DU SHORTCODE POUR LE FORMULAIRE DE RÉSERVATION
 */
function reservation_form_full_south() {
    ob_start();
    ?>
    <div class="reservation-form-container">
        <form method="post">
            
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" name="first_name" id="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" name="last_name" id="last_name" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone (WhatsApp) *</label>
                <input type="text" name="phone" id="phone" placeholder="+12 34 567 80" required>
            </div>

            <div class="form-group">
                <label for="travel_date">Travel Date *</label>
                <input type="date" name="travel_date" id="travel_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="travelers">Number of Travelers *</label>
                <input type="number" name="travelers" id="travelers" min="1" required>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea name="message" id="message" rows="4"></textarea>
            </div>
            <input type="hidden" name="reservation_source" value="SOUTHERN MADAGASCAR EXPLORATION"> <!-- Champ caché pour identifier la source -->

            <button type="submit" name="submit_reservation">Send Reservation</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('reservation_form_full_south', 'reservation_form_full_south');
function reservation_form_full_east() {
    ob_start();
    ?>
    <div class="reservation-form-container">
        <form method="post">
            
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" name="first_name" id="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" name="last_name" id="last_name" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone (WhatsApp) *</label>
                <input type="text" name="phone" id="phone" placeholder="+12 34 567 80" required>
            </div>

            <div class="form-group">
                <label for="travel_date">Travel Date *</label>
                <input type="date" name="travel_date" id="travel_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="travelers">Number of Travelers *</label>
                <input type="number" name="travelers" id="travelers" min="1" required>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea name="message" id="message" rows="4"></textarea>
            </div>
            <input type="hidden" name="reservation_source" value="MADAGSCAR EASTERN DISCOVERY"> <!-- Champ caché pour identifier la source -->

            <button type="submit" name="submit_reservation">Send Reservation</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('reservation_form_full_east', 'reservation_form_full_east');


/**
 * 6. ENREGISTREMENT DES RÉSERVATIONS
 */
function save_reservation_full() {
    if(isset($_POST['submit_reservation'])){

        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name  = sanitize_text_field($_POST['last_name']);
        $email      = sanitize_email($_POST['email']);
        $phone      = sanitize_text_field($_POST['phone']);
        $travel_date= sanitize_text_field($_POST['travel_date']);
        $travelers  = intval($_POST['travelers']);
        $message    = sanitize_textarea_field($_POST['message']);
        $tour       = sanitize_text_field($_POST['tour']);

        $post_id = wp_insert_post([
            'post_title'  => $first_name . ' ' . $last_name,
            'post_type'   => 'reservation',
            'post_status' => 'publish'
        ]);

        if($post_id){
            update_post_meta($post_id,'first_name',$first_name);
            update_post_meta($post_id,'last_name',$last_name);
            update_post_meta($post_id,'email',$email);
            update_post_meta($post_id,'phone',$phone);
            update_post_meta($post_id,'travel_date',$travel_date);
            update_post_meta($post_id,'travelers',$travelers);
            update_post_meta($post_id,'message',$message);
            update_post_meta($post_id,'tour',$tour);
            update_post_meta($post_id,'reservation_status','Pending');
            // Envoyer les emails
            send_reservation_emails($post_id);
        }
    }
}
add_action('init','save_reservation_full');



/**
 * 7. AFFICHAGE DU STATUS DE RÉSERVATION DANS LE TABLEAU ADMIN
 */
function reservation_admin_styles() {
    echo '
    <style>
        .reservation-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            color: #fff;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
        }
        .reservation-status.pending { background-color: #6c757d; } /* gris */
        .reservation-status.availability-confirmed { background-color: #0d6efd; } /* bleu */
        .reservation-status.deposit-requested { background-color: #ffc107; color: #000; } /* jaune */
        .reservation-status.deposit-received { background-color: #20c997; } /* vert clair */
        .reservation-status.confirmed { background-color: #198754; } /* vert foncé */
        .reservation-status.cancelled { background-color: #dc3545; } /* rouge */
    </style>
    ';
}
add_action('admin_head','reservation_admin_styles');



/**
 * 8. COLONNES PERSONNALISÉES DANS LE TABLEAU ADMIN DES RÉSERVATIONS
 */
// Colonnes dans le tableau admin
function set_reservation_columns($columns){
    $columns = array(
        'cb'           => '<input type="checkbox" />',
        'title'        => 'Client',
        'email'        => 'Email',
        'phone'        => 'Phone',
        'tour'         => 'Tour',
        'travel_date'  => 'Travel Date',
        'travelers'    => 'Number of Travelers',
        'message'      => 'Message',
        'status'       => 'Status',
        'date'         => 'Submitted'
    );
    return $columns;
}
add_filter('manage_reservation_posts_columns', 'set_reservation_columns', 11);

/** 
 * 9. CONTENU DES COLONNES PERSONNALISÉES
*/
function custom_reservation_column_content($column,$post_id){
    switch($column){
        case 'email': echo get_post_meta($post_id,'email',true); break;
        case 'tour': echo get_post_meta($post_id,'tour',true); break;
        case 'phone': echo get_post_meta($post_id,'phone',true); break;
        case 'travel_date': echo get_post_meta($post_id,'travel_date',true); break;
        case 'travelers': echo get_post_meta($post_id,'travelers',true); break;
        case 'message': echo wp_trim_words(get_post_meta($post_id,'message',true),10,'…'); break;
        case 'status': 
            $status = get_post_meta($post_id,'reservation_status',true);
            if(!$status) $status = 'Pending';
            $status_class = strtolower(str_replace(' ','-',$status));
            echo '<span class="reservation-status '.$status_class.'">'.$status.'</span>';
            break;
    }
}
add_action('manage_reservation_posts_custom_column','custom_reservation_column_content',10,2);


/**
 * 10. FILTRES COLONNES PERSONNALISÉES DANS LE TABLEAU ADMIN DES RÉSERVATIONS
 */
// Afficher les filtres dans l'admin avec labels fonctionnels
function reservation_admin_filters_fixed() {
    global $typenow;
    if ($typenow != 'reservation') return;

    // --- Status Filter ---
    $current_status = isset($_GET['reservation_status_filter']) ? $_GET['reservation_status_filter'] : '';
    $statuses = get_reservation_statuses();
    echo '<select name="reservation_status_filter">';
    echo '<option value="">All Status</option>';
    foreach($statuses as $key => $label){
        echo '<option value="'.esc_attr($key).'" '.selected($current_status,$key,false).'>'.esc_html($label).'</option>';
    }
    echo '</select>';

    // --- Travel Date Filter ---
    global $wpdb;
    // Récupérer toutes les années et mois uniques
    $all_dates = $wpdb->get_results("
        SELECT DISTINCT YEAR(meta_value) as year, MONTH(meta_value) as month
        FROM {$wpdb->postmeta} pm
        JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'travel_date'
        ORDER BY year DESC, month DESC
    ");

    // Extraire années et mois uniques
    $years = [];
    $months = [];
    foreach($all_dates as $d){
        $years[] = $d->year;
        $months[$d->month] = date('F', mktime(0,0,0,$d->month,1)); // mois en texte
    }
    $years = array_unique($years);

    // Valeurs actuellement sélectionnées
    $current_year = isset($_GET['travel_year']) ? $_GET['travel_year'] : '';
    $current_month = isset($_GET['travel_month']) ? $_GET['travel_month'] : '';

    // Affichage des selects avec texte descriptif
    echo '<select name="travel_year">';
    echo '<option value="">Choose a Travel Year</option>'; // label visible
    foreach($years as $year){
        echo '<option value="'.$year.'" '.selected($current_year,$year,false).'>'.$year.'</option>';
    }
    echo '</select>';

    echo '<select name="travel_month">';
    echo '<option value="">Choose a Travel Month</option>'; // label visible
    foreach($months as $num => $name){
        echo '<option value="'.$num.'" '.selected($current_month,$num,false).'>'.$name.'</option>';
    }
    echo '</select>';
}
add_action('restrict_manage_posts','reservation_admin_filters_fixed');


/**
 * 11. QUERY DE FILTRAGE DANS LE TABLEAU ADMIN DES RÉSERVATIONS
 */
function reservation_admin_filter_query($query){
    global $pagenow, $wpdb;
    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
    if($post_type != 'reservation' || $pagenow != 'edit.php') return;

    // Filter Status
    if(!empty($_GET['reservation_status_filter'])){
        $query->set('meta_key','reservation_status');
        $query->set('meta_value',sanitize_text_field($_GET['reservation_status_filter']));
    }

    // Filter Travel Date
    if(!empty($_GET['travel_year']) || !empty($_GET['travel_month'])){
        $meta_query = array('relation'=>'AND');
        if(!empty($_GET['travel_year'])){
            $meta_query[] = array(
                'key' => 'travel_date',
                'value' => $_GET['travel_year'],
                'compare' => 'LIKE'
            );
        }
        if(!empty($_GET['travel_month'])){
            $month = str_pad($_GET['travel_month'],2,'0',STR_PAD_LEFT);
            $meta_query[] = array(
                'key' => 'travel_date',
                'value' => '-'.$month.'-',
                'compare' => 'LIKE'
            );
        }
        $query->set('meta_query',$meta_query);
    }

}
add_filter('pre_get_posts','reservation_admin_filter_query');


/**
 * 12. EMAIL DE CONFIRMATION POUR LE CLIENT ET L'ADMIN APRÈS LA RÉSERVATION
 */
function send_reservation_emails($reservation_id){
    if(!$reservation_id) return;

    // Infos de la réservation
    $client_name  = get_post_meta($reservation_id,'first_name',true).' '.get_post_meta($reservation_id,'last_name',true);
    $client_email = get_post_meta($reservation_id,'email',true);
    $travel_date  = get_post_meta($reservation_id,'travel_date',true);
    $travelers    = get_post_meta($reservation_id,'travelers',true);
    $message      = get_post_meta($reservation_id,'message',true);

    // --- Admin Email ---
    $admin_email = get_option('admin_email');
    $subject = 'New Reservation: '.$client_name;
    $body = "You have received a new reservation:\n\n";
    $body .= "Client: $client_name\n";
    $body .= "Email: $client_email\n";
    $body .= "Travel Date: $travel_date\n";
    $body .= "Number of Travelers: $travelers\n";
    $body .= "Message: $message\n";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    wp_mail($admin_email, $subject, $body, $headers);

    // --- Client Email ---
    if($client_email){
        $subject_client = 'Your Reservation Confirmation';
        $body_client = "Hello $client_name,\n\n";
        $body_client .= "Thank you for your reservation. Here is the summary:\n";
        $body_client .= "Travel Date: $travel_date\n";
        $body_client .= "Number of Travelers: $travelers\n";
        $body_client .= "Message: $message\n\n";
        $body_client .= "We will contact you soon. We respond within 24 hours.\n\nBest regards,\nRabe Dago Tour";
        
        wp_mail($client_email, $subject_client, $body_client, $headers);
}
}


/**
 * 13. WHATSAPP CONFIRMAION LINK IN ADMIN RESERVATION TABLE
 */

// Ajouter la colonne WhatsApp dans le tableau admin
add_filter('manage_reservation_posts_columns', function($columns){
    $columns['whatsapp'] = 'WhatsApp';
    return $columns;
}, 12); // priorité 12 pour que ce soit après tes colonnes

add_action('manage_reservation_posts_custom_column', function($column, $post_id){
    if($column === 'whatsapp'){
        $phone = get_post_meta($post_id,'phone',true);
        $first_name = get_post_meta($post_id,'first_name',true);
        $last_name = get_post_meta($post_id,'last_name',true);
        $travel_date = get_post_meta($post_id,'travel_date',true);
        $travelers = get_post_meta($post_id,'travelers',true);
        $email = get_post_meta($post_id,'email',true);
        $message_text = get_post_meta($post_id,'message',true);

        if(!empty($phone)){
            // Nettoyer le numéro pour ne garder que les chiffres
            $phone_clean = preg_replace('/\D/', '', $phone);

            // Message personnalisé
           // Message pré-rempli avec les informations du client
           // Pour l'instant on envoie juste les infos de base, mais tu peux personnaliser selon tes besoins
            $message = urlencode(
                "Client Profile Info:\n" .
                "Name: $first_name $last_name\n" .
                "Email: $email\n" .
                "Phone: $phone\n" .
                "Travel Date: $travel_date\n" .
                "Number of Travelers: $travelers\n" .
                "Message: $message_text"
            );

            // Lien wa.me
            $wa_link = "https://wa.me/$phone_clean?text=$message";

            // Icône SVG WhatsApp
            $svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#fff" viewBox="0 0 24 24">
                <path d="M20.52 3.48A11.94 11.94 0 0012 0C5.372 0 0 5.372 0 12a11.956 11.956 0 001.66 6.024L0 24l6.024-1.66A11.956 11.956 0 0012 24c6.628 0 12-5.372 12-12 0-3.19-1.246-6.188-3.48-8.52zm-8.52 18c-1.93 0-3.73-.51-5.314-1.398l-.38-.224-3.58.988.964-3.496-.245-.384A9.954 9.954 0 012 12c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10zm5.453-6.495c-.29-.145-1.72-.847-1.985-.944-.266-.098-.46-.145-.654.145-.194.29-.75.944-.918 1.14-.168.194-.335.218-.624.073-.29-.145-1.225-.452-2.33-1.436-.863-.769-1.445-1.719-1.616-2.01-.168-.29-.018-.447.127-.592.13-.13.29-.335.435-.503.145-.168.194-.29.29-.483.098-.194.05-.364-.024-.51-.073-.145-.654-1.575-.896-2.155-.236-.564-.477-.487-.654-.497l-.56-.01c-.194 0-.51.073-.777.364s-1.018.993-1.018 2.422c0 1.43 1.042 2.812 1.187 3.006.145.194 2.05 3.125 4.975 4.385.695.3 1.235.48 1.655.615.695.22 1.33.19 1.83.115.558-.084 1.72-.7 1.965-1.376.245-.677.245-1.256.17-1.376-.073-.122-.266-.194-.556-.338z"/>
            </svg>';

            echo '<a href="'.$wa_link.'" target="_blank" class="button" style="background-color:#25D366;border-color:#25D366;">'.$svg_icon.'</a>';
       
        } else {
            echo 'No number';
        }
    }
}, 11, 2); // priorité 11 pour exécuter après ton code existant
