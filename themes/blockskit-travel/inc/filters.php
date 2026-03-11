<?php

/**
 * Filters - Rabe Dago Tour
 * Filtres admin + colonnes + WhatsApp
 */


// ============================================================
// 10. FILTRES DANS LE TABLEAU ADMIN
// ============================================================
function reservation_admin_filters_fixed() {
    global $typenow;
    if ($typenow != RDT_CPT_SLUG) return;

    // --- Filtre Status ---
    $current_status = isset($_GET['reservation_status_filter']) ? $_GET['reservation_status_filter'] : '';
    $statuses = get_reservation_statuses();
    echo '<select name="reservation_status_filter">';
    echo '<option value="">All Status</option>';
    foreach ($statuses as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($current_status, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';

    // --- Filtre Tour ---
    global $wpdb;
    $current_tour = isset($_GET['reservation_tour_filter']) ? $_GET['reservation_tour_filter'] : '';
    $tours = $wpdb->get_col("
        SELECT DISTINCT meta_value
        FROM {$wpdb->postmeta} pm
        JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'reservation_source'
        AND p.post_type = '" . RDT_CPT_SLUG . "'
        AND p.post_status = 'publish'
        ORDER BY meta_value ASC
    ");

    echo '<select name="reservation_tour_filter">';
    echo '<option value="">All Tours</option>';
    foreach ($tours as $tour) {
        echo '<option value="' . esc_attr($tour) . '" ' . selected($current_tour, $tour, false) . '>' . esc_html($tour) . '</option>';
    }
    echo '</select>';

    // --- Filtre Travel Date (Année + Mois) ---
    $all_dates = $wpdb->get_results("
        SELECT DISTINCT YEAR(meta_value) as year, MONTH(meta_value) as month
        FROM {$wpdb->postmeta} pm
        JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'travel_date'
        AND p.post_type = '" . RDT_CPT_SLUG . "'
        ORDER BY year DESC, month DESC
    ");

    $years  = [];
    $months = [];
    foreach ($all_dates as $d) {
        $years[]           = $d->year;
        $months[$d->month] = date('F', mktime(0, 0, 0, $d->month, 1));
    }
    $years = array_unique($years);

    $current_year  = isset($_GET['travel_year'])  ? $_GET['travel_year']  : '';
    $current_month = isset($_GET['travel_month']) ? $_GET['travel_month'] : '';

    echo '<select name="travel_year">';
    echo '<option value="">All Years</option>';
    foreach ($years as $year) {
        echo '<option value="' . $year . '" ' . selected($current_year, $year, false) . '>' . $year . '</option>';
    }
    echo '</select>';

    echo '<select name="travel_month">';
    echo '<option value="">All Months</option>';
    foreach ($months as $num => $name) {
        echo '<option value="' . $num . '" ' . selected($current_month, $num, false) . '>' . $name . '</option>';
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'reservation_admin_filters_fixed');


// ============================================================
// 11. QUERY DE FILTRAGE
// ============================================================
function reservation_admin_filter_query($query) {
    global $pagenow;
    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
    if ($post_type != RDT_CPT_SLUG || $pagenow != 'edit.php') return;

    $meta_query = ['relation' => 'AND'];

    // Filtre Status
    if (!empty($_GET['reservation_status_filter'])) {
        $meta_query[] = [
            'key'   => 'reservation_status',
            'value' => sanitize_text_field($_GET['reservation_status_filter']),
        ];
    }

    // Filtre Tour
    if (!empty($_GET['reservation_tour_filter'])) {
        $meta_query[] = [
            'key'   => 'reservation_source',
            'value' => sanitize_text_field($_GET['reservation_tour_filter']),
        ];
    }

    // Filtre Travel Date
    if (!empty($_GET['travel_year'])) {
        $meta_query[] = [
            'key'     => 'travel_date',
            'value'   => sanitize_text_field($_GET['travel_year']),
            'compare' => 'LIKE',
        ];
    }
    if (!empty($_GET['travel_month'])) {
        $month = str_pad(intval($_GET['travel_month']), 2, '0', STR_PAD_LEFT);
        $meta_query[] = [
            'key'     => 'travel_date',
            'value'   => '-' . $month . '-',
            'compare' => 'LIKE',
        ];
    }

    if (count($meta_query) > 1) {
        $query->set('meta_query', $meta_query);
    }
}
add_filter('pre_get_posts', 'reservation_admin_filter_query');


// ============================================================
// 13. COLONNE WHATSAPP
// ============================================================
add_filter('manage_reservation_posts_columns', function ($columns) {
    $columns['whatsapp'] = 'WhatsApp';
    return $columns;
}, 12);

add_action('manage_reservation_posts_custom_column', function ($column, $post_id) {
    if ($column !== 'whatsapp') return;

    $phone       = get_post_meta($post_id, 'phone',              true);
    $first_name  = get_post_meta($post_id, 'first_name',         true);
    $last_name   = get_post_meta($post_id, 'last_name',          true);
    $email       = get_post_meta($post_id, 'email',              true);
    $travel_date = get_post_meta($post_id, 'travel_date',        true);
    $travelers   = get_post_meta($post_id, 'travelers',          true);
    $tour        = get_post_meta($post_id, 'reservation_source', true);
    $status      = get_post_meta($post_id, 'reservation_status', true) ?: 'Pending';
    $message_txt = get_post_meta($post_id, 'message',            true);

    if (empty($phone)) {
        echo '<span style="color:#999;">No number</span>';
        return;
    }

    $phone_clean = preg_replace('/\D/', '', $phone);

    $wa_text = urlencode(
        "Hello $first_name $last_name,\n\n" .
        "Here is a summary of your reservation:\n" .
        "———————————————\n" .
        "🗺️ Tour: $tour\n" .
        "📅 Travel Date: $travel_date\n" .
        "👥 Travelers: $travelers\n" .
        "📧 Email: $email\n" .
        "📞 Phone: $phone\n" .
        "🔖 Status: $status\n" .
        (! empty($message_txt) ? "💬 Message: $message_txt\n" : '') .
        "———————————————\n\n" .
        "Thank you for booking with " . RDT_COMPANY_NAME . "! 🌿"
    );

    $wa_link = "https://wa.me/{$phone_clean}?text={$wa_text}";

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#fff" viewBox="0 0 24 24">
        <path d="M20.52 3.48A11.94 11.94 0 0012 0C5.372 0 0 5.372 0 12a11.956 11.956 0 001.66 6.024L0 24l6.024-1.66A11.956 11.956 0 0012 24c6.628 0 12-5.372 12-12 0-3.19-1.246-6.188-3.48-8.52zm-8.52 18c-1.93 0-3.73-.51-5.314-1.398l-.38-.224-3.58.988.964-3.496-.245-.384A9.954 9.954 0 012 12c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10zm5.453-6.495c-.29-.145-1.72-.847-1.985-.944-.266-.098-.46-.145-.654.145-.194.29-.75.944-.918 1.14-.168.194-.335.218-.624.073-.29-.145-1.225-.452-2.33-1.436-.863-.769-1.445-1.719-1.616-2.01-.168-.29-.018-.447.127-.592.13-.13.29-.335.435-.503.145-.168.194-.29.29-.483.098-.194.05-.364-.024-.51-.073-.145-.654-1.575-.896-2.155-.236-.564-.477-.487-.654-.497l-.56-.01c-.194 0-.51.073-.777.364s-1.018.993-1.018 2.422c0 1.43 1.042 2.812 1.187 3.006.145.194 2.05 3.125 4.975 4.385.695.3 1.235.48 1.655.615.695.22 1.33.19 1.83.115.558-.084 1.72-.7 1.965-1.376.245-.677.245-1.256.17-1.376-.073-.122-.266-.194-.556-.338z"/>
    </svg>';

    echo '<a href="' . $wa_link . '" target="_blank" class="button" style="background:#25D366; border-color:#25D366; padding:4px 10px; display:inline-flex; align-items:center; gap:6px;">'
        . $svg
        . '<span style="color:#fff; font-size:12px;">Contact</span>'
        . '</a>';

}, 11, 2);