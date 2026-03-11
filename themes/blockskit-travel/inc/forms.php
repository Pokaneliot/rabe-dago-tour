<?php

/**
 * Forms - Rabe Dago Tour
 * Formulaire de réservation universel
 *
 * UTILISATION SHORTCODE :
 * [reservation_form tour="SOUTHERN MADAGASCAR EXPLORATION"]
 * [reservation_form tour="MADAGASCAR EASTERN DISCOVERY"]
 * [reservation_form tour="MON NOUVEAU TOUR"]  ← ajouter un tour = juste changer le paramètre
 */


// ============================================================
// 1. SHORTCODE UNIVERSEL
// ============================================================
function reservation_form_shortcode($atts) {

    // Paramètre du shortcode avec valeur par défaut
    $atts = shortcode_atts(
        array('tour' => 'TOUR'),
        $atts,
        'reservation_form'
    );

    $tour = sanitize_text_field($atts['tour']);

    ob_start();

    // --- Message de succès ---
    if (isset($_GET['reservation']) && $_GET['reservation'] === 'success') {
        ?>
        <div class="rdt-success-message" style="
            background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
            border-left: 4px solid #43a047;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        ">
            <span style="font-size:28px; line-height:1;">✅</span>
            <div>
                <p style="margin:0 0 4px; font-weight:700; color:#2e7d32; font-size:16px;">
                    Reservation sent successfully!
                </p>
                <p style="margin:0; color:#555; font-size:14px;">
                    Thank you! We will get back to you within <strong>24 hours</strong> to confirm your booking.
                </p>
            </div>
        </div>
        <?php
    }

    ?>
    <div class="rdt-form-wrapper">
        <form method="post" id="rdt-reservation-form" novalidate>

            <!-- Champ caché : identifie le tour -->
            <input type="hidden" name="reservation_source" value="<?php echo esc_attr($tour); ?>">
            <!-- Nonce sécurité -->
            <?php wp_nonce_field('rdt_reservation_nonce', 'rdt_nonce'); ?>

            <div class="rdt-form-row">
                <div class="rdt-form-group">
                    <label for="rdt_first_name">First Name <span>*</span></label>
                    <input type="text" name="first_name" id="rdt_first_name" placeholder="John" required>
                </div>
                <div class="rdt-form-group">
                    <label for="rdt_last_name">Last Name <span>*</span></label>
                    <input type="text" name="last_name" id="rdt_last_name" placeholder="Doe" required>
                </div>
            </div>

            <div class="rdt-form-row">
                <div class="rdt-form-group">
                    <label for="rdt_email">Email <span>*</span></label>
                    <input type="email" name="email" id="rdt_email" placeholder="john@example.com" required>
                </div>
                <div class="rdt-form-group">
                    <label for="rdt_phone">Phone (WhatsApp) <span>*</span></label>
                    <input type="text" name="phone" id="rdt_phone" placeholder="+1 234 567 890" required>
                </div>
            </div>

            <div class="rdt-form-row">
                <div class="rdt-form-group">
                    <label for="rdt_travel_date">Travel Date <span>*</span></label>
                    <input type="date" name="travel_date" id="rdt_travel_date"
                        required min="<?php echo esc_attr(date('Y-m-d')); ?>">
                </div>
                <div class="rdt-form-group">
                    <label for="rdt_travelers">Number of Travelers <span>*</span></label>
                    <input type="number" name="travelers" id="rdt_travelers" placeholder="2" min="1" required>
                </div>
            </div>

            <div class="rdt-form-group">
                <label for="rdt_message">Message <span class="optional">(optional)</span></label>
                <textarea name="message" id="rdt_message" rows="4"
                    placeholder="Tell us about your expectations, special requests..."></textarea>
            </div>

            <button type="submit" name="submit_reservation" class="rdt-submit-btn">
                <span>✈️ Send My Reservation</span>
            </button>

        </form>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('reservation_form', 'reservation_form_shortcode');

// Shortcodes legacy (pour ne pas casser les pages existantes)
add_shortcode('reservation_form_full_south', function() {
    return do_shortcode('[reservation_form tour="SOUTHERN MADAGASCAR EXPLORATION"]');
});
add_shortcode('reservation_form_full_east', function() {
    return do_shortcode('[reservation_form tour="MADAGASCAR EASTERN DISCOVERY"]');
});


// ============================================================
// 2. ENREGISTREMENT DE LA RÉSERVATION
// ============================================================
function save_reservation_full() {
    if (!isset($_POST['submit_reservation'])) return;

    // Vérification nonce
    if (!isset($_POST['rdt_nonce']) || !wp_verify_nonce($_POST['rdt_nonce'], 'rdt_reservation_nonce')) {
        wp_die('Security check failed.');
    }

    // Sanitize les données
    $first_name  = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name   = sanitize_text_field($_POST['last_name'] ?? '');
    $email       = sanitize_email($_POST['email'] ?? '');
    $phone       = sanitize_text_field($_POST['phone'] ?? '');
    $travel_date = sanitize_text_field($_POST['travel_date'] ?? '');
    $travelers   = intval($_POST['travelers'] ?? 1);
    $message     = sanitize_textarea_field($_POST['message'] ?? '');
    $tour        = sanitize_text_field($_POST['reservation_source'] ?? '');

    // Validation minimale
    if (empty($first_name) || empty($last_name) || empty($email) || empty($travel_date)) return;

    // Création du post
    $post_id = wp_insert_post([
        'post_title'  => $first_name . ' ' . $last_name,
        'post_type'   => 'reservation',
        'post_status' => 'publish',
    ]);

    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, 'first_name',          $first_name);
        update_post_meta($post_id, 'last_name',           $last_name);
        update_post_meta($post_id, 'email',               $email);
        update_post_meta($post_id, 'phone',               $phone);
        update_post_meta($post_id, 'travel_date',         $travel_date);
        update_post_meta($post_id, 'travelers',           $travelers);
        update_post_meta($post_id, 'message',             $message);
        update_post_meta($post_id, 'reservation_source',  $tour);
        update_post_meta($post_id, 'reservation_status',  'Pending');

        // Envoi des emails
        send_reservation_emails($post_id);

        // Redirection avec message de succès (vide le formulaire)
        $redirect = add_query_arg('reservation', 'success', wp_get_referer() ?: get_permalink());
        wp_safe_redirect($redirect);
        exit;
    }
}
add_action('init', 'save_reservation_full');


