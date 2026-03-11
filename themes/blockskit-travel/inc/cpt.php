<?php

/**
 * CPT - Rabe Dago Tour
 * Custom Post Type, Statuts, Meta Boxes, Colonnes Admin
 */


// ============================================================
// 1. CRÉATION DU CUSTOM POST TYPE
// ============================================================
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
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => RDT_CPT_SLUG),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-calendar-alt',
        'supports'           => array('title'),
        'show_in_rest'       => true,
    );

    register_post_type(RDT_CPT_SLUG, $args);
}
add_action('init', 'create_reservation_cpt');


// ============================================================
// 2. STATUTS DE RÉSERVATION
// ============================================================
function get_reservation_statuses() {
    return array(
        'Pending'                => 'Pending',
        'Availability Confirmed' => 'Availability Confirmed',
        'Deposit Requested'      => 'Deposit Requested',
        'Deposit Received'       => 'Deposit Received',
        'Confirmed'              => 'Confirmed',
        'Cancelled'              => 'Cancelled',
    );
}


// ============================================================
// 3. META BOX — Modifier le statut dans l'admin
// ============================================================
function reservation_status_meta_box() {
    add_meta_box(
        'reservation_status_box',
        'Reservation Status',
        'reservation_status_box_html',
        RDT_CPT_SLUG,
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'reservation_status_meta_box');

function reservation_status_box_html($post) {
    $statuses = get_reservation_statuses();
    $current  = get_post_meta($post->ID, 'reservation_status', true) ?: 'Pending';
    ?>
    <select name="reservation_status" style="width:100%;">
        <?php foreach ($statuses as $key => $label) : ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($current, $key); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p style="margin:10px 0 0; font-size:12px; color:#888;">
        ⚠️ Changing to <strong>Availability Confirmed</strong> or <strong>Deposit Received</strong> will automatically send an email to the client.
    </p>
    <?php
}


// ============================================================
// 4. SAUVEGARDE DU STATUT + WORKFLOW EMAILS
// ============================================================
function save_reservation_status($post_id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_POST['reservation_status'])) return;

    $valid_statuses   = array_keys(get_reservation_statuses());
    $requested_status = sanitize_text_field($_POST['reservation_status']);
    $old_status       = get_post_meta($post_id, 'reservation_status', true);

    if (!in_array($requested_status, $valid_statuses)) return;

    // Workflow : Availability Confirmed → passe auto à Deposit Requested + email
    if ($requested_status === 'Availability Confirmed') {
        update_post_meta($post_id, 'reservation_status', 'Deposit Requested');
        sendMailAvailabilityConfirmed($post_id, $post);

    // Workflow : Deposit Received (seulement si statut précédent = Deposit Requested) → passe auto à Confirmed + email
    } elseif ($requested_status === 'Deposit Received' && $old_status === 'Deposit Requested') {
        update_post_meta($post_id, 'reservation_status', 'Confirmed');
        sendMailDepositReceived($post_id, $post);

    // Tous les autres statuts : sauvegarde normale
    } else {
        update_post_meta($post_id, 'reservation_status', $requested_status);
    }
}
add_action('save_post_reservation', 'save_reservation_status', 10, 2);


// ============================================================
// 7. STYLES DES BADGES DE STATUT DANS L'ADMIN
// ============================================================
function reservation_admin_styles() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== RDT_CPT_SLUG) return; // charger seulement sur les pages du CPT
    ?>
    <style>
        .reservation-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            color: #fff;
            font-weight: 600;
            font-size: 12px;
            text-align: center;
            min-width: 110px;
            white-space: nowrap;
        }
        .reservation-status.pending               { background-color: #6c757d; }
        .reservation-status.availability-confirmed { background-color: #0d6efd; }
        .reservation-status.deposit-requested     { background-color: #e8a020; color: #fff; }
        .reservation-status.deposit-received      { background-color: #20c997; }
        .reservation-status.confirmed             { background-color: #198754; }
        .reservation-status.cancelled             { background-color: #dc3545; }
    </style>
    <?php
}
add_action('admin_head', 'reservation_admin_styles');


// ============================================================
// 8. COLONNES PERSONNALISÉES
// ============================================================
function set_reservation_columns($columns) {
    return array(
        'cb'          => '<input type="checkbox" />',
        'title'       => 'Client',
        'email'       => 'Email',
        'phone'       => 'Phone',
        'tour'        => 'Tour',              // ← affiché via reservation_source
        'travel_date' => 'Travel Date',
        'travelers'   => 'Travelers',
        'message'     => 'Message',
        'status'      => 'Status',
        'date'        => 'Submitted',
    );
}
add_filter('manage_reservation_posts_columns', 'set_reservation_columns', 11);


// ============================================================
// 9. CONTENU DES COLONNES
// ============================================================
function custom_reservation_column_content($column, $post_id) {
    switch ($column) {
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
            break;

        case 'tour':
            // ✅ BUGFIX : la meta key est 'reservation_source', pas 'tour'
            $tour = get_post_meta($post_id, 'reservation_source', true);
            echo $tour ? esc_html($tour) : '<span style="color:#bbb;">—</span>';
            break;

        case 'phone':
            echo esc_html(get_post_meta($post_id, 'phone', true));
            break;

        case 'travel_date':
            echo esc_html(get_post_meta($post_id, 'travel_date', true));
            break;

        case 'travelers':
            echo esc_html(get_post_meta($post_id, 'travelers', true));
            break;

        case 'message':
            $msg = get_post_meta($post_id, 'message', true);
            echo $msg ? esc_html(wp_trim_words($msg, 8, '…')) : '<span style="color:#bbb;">—</span>';
            break;

        case 'status':
            $status       = get_post_meta($post_id, 'reservation_status', true) ?: 'Pending';
            $status_class = strtolower(str_replace(' ', '-', $status));
            echo '<span class="reservation-status ' . esc_attr($status_class) . '">' . esc_html($status) . '</span>';
            break;
    }
}
add_action('manage_reservation_posts_custom_column', 'custom_reservation_column_content', 10, 2);