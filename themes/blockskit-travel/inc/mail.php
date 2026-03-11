<?php

/**
 * Mail Functions - Rabe Dago Tour
 * Emails HTML professionnels — thème Bleu foncé #1a2e4a + Orange #e8621a
 */


// ============================================================
// HELPER : Template HTML de base pour tous les emails
// ============================================================
function rdt_email_template($title, $content) {
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>' . esc_html($title) . '</title>
</head>
<body style="margin:0; padding:0; background-color:#eef2f7; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7; padding:40px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 4px 24px rgba(26,46,74,0.13); max-width:600px;">

                    <!-- Header bleu foncé -->
                    <tr>
                        <td style="background:linear-gradient(135deg, #1a2e4a 0%, #1e3a5f 60%, #2a4a7a 100%); padding:40px 40px 32px; text-align:center;">
                            <div style="display:inline-block; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:50px; padding:6px 20px; margin-bottom:16px;">
                                <span style="font-size:12px; color:rgba(255,255,255,0.7); letter-spacing:3px; text-transform:uppercase;">Madagascar</span>
                            </div>
                            <h1 style="margin:0 0 10px; color:#ffffff; font-size:30px; font-weight:700; letter-spacing:1px;">' . RDT_COMPANY_NAME . '</h1>
                            <div style="width:40px; height:3px; background:#e8621a; border-radius:2px; margin:0 auto 14px;"></div>
                            <p style="margin:0; color:rgba(255,255,255,0.75); font-size:14px;">🌿 Discover the Wonders of Madagascar</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 40px 32px;">
                            ' . $content . '
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f4f7fb; padding:24px 40px; text-align:center; border-top:3px solid #e8621a;">
                            <p style="margin:0 0 4px; font-size:13px; color:#888;">Questions? Contact us anytime</p>
                            <p style="margin:0 0 12px; font-size:14px; color:#e8621a; font-weight:700;">' . RDT_COMPANY_EMAIL . '</p>
                            <p style="margin:0; font-size:11px; color:#bbb;">© ' . date('Y') . ' ' . RDT_COMPANY_NAME . ' · Madagascar</p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>';
}


// ============================================================
// HELPER : Tableau récapitulatif de réservation
// ============================================================
function rdt_reservation_summary($travel_date, $travelers, $message = '') {

    $rows = '
    <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8edf5; border-radius:10px; overflow:hidden; margin:20px 0;">
        <tr>
            <td style="padding:14px 20px; background:#f4f7fb; border-bottom:1px solid #e8edf5;">
                <span style="font-size:11px; color:#888; display:block; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px;">📅 Travel Date</span>
                <span style="font-size:16px; color:#1a2e4a; font-weight:700;">' . esc_html($travel_date) . '</span>
            </td>
        </tr>
        <tr>
            <td style="padding:14px 20px; background:#ffffff;' . (empty($message) ? '' : ' border-bottom:1px solid #e8edf5;') . '">
                <span style="font-size:11px; color:#888; display:block; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px;">👥 Travelers</span>
                <span style="font-size:16px; color:#1a2e4a; font-weight:700;">' . esc_html($travelers) . ' traveler(s)</span>
            </td>
        </tr>';

    if (!empty($message)) {
        $rows .= '
        <tr>
            <td style="padding:14px 20px; background:#f4f7fb;">
                <span style="font-size:11px; color:#888; display:block; margin-bottom:3px; text-transform:uppercase; letter-spacing:1px;">💬 Message</span>
                <span style="font-size:15px; color:#1a2e4a;">' . esc_html($message) . '</span>
            </td>
        </tr>';
    }

    $rows .= '</table>';
    return $rows;
}


// ============================================================
// EMAIL 1 : Nouvelle réservation (Admin + Client)
// ============================================================
function send_reservation_emails($reservation_id) {
    if (!$reservation_id) return;

    $first_name   = get_post_meta($reservation_id, 'first_name', true);
    $last_name    = get_post_meta($reservation_id, 'last_name', true);
    $client_name  = $first_name . ' ' . $last_name;
    $client_email = get_post_meta($reservation_id, 'email', true);
    $phone        = get_post_meta($reservation_id, 'phone', true);
    $travel_date  = get_post_meta($reservation_id, 'travel_date', true);
    $travelers    = get_post_meta($reservation_id, 'travelers', true);
    $message      = get_post_meta($reservation_id, 'message', true);
    $tour         = get_post_meta($reservation_id, 'reservation_source', true);
    $headers      = ['Content-Type: text/html; charset=UTF-8'];

    // --- Email Admin ---
    $admin_content = '
        <h2 style="margin:0 0 6px; color:#1a2e4a; font-size:22px; font-weight:700;">🔔 New Reservation!</h2>
        <p style="margin:0 0 24px; color:#666; font-size:15px; border-left:3px solid #e8621a; padding-left:12px;">
            A new booking request just came in. Review the details below.
        </p>

        <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8edf5; border-radius:10px; overflow:hidden; margin-bottom:24px;">
            <tr><td style="padding:12px 20px; background:#f4f7fb; border-bottom:1px solid #e8edf5;">
                <span style="font-size:11px;color:#888;display:block;text-transform:uppercase;letter-spacing:1px;">👤 Client</span>
                <strong style="color:#1a2e4a;">' . esc_html($client_name) . '</strong>
            </td></tr>
            <tr><td style="padding:12px 20px; background:#fff; border-bottom:1px solid #e8edf5;">
                <span style="font-size:11px;color:#888;display:block;text-transform:uppercase;letter-spacing:1px;">✉️ Email</span>
                <strong style="color:#1a2e4a;">' . esc_html($client_email) . '</strong>
            </td></tr>
            <tr><td style="padding:12px 20px; background:#f4f7fb; border-bottom:1px solid #e8edf5;">
                <span style="font-size:11px;color:#888;display:block;text-transform:uppercase;letter-spacing:1px;">📞 Phone (WhatsApp)</span>
                <strong style="color:#1a2e4a;">' . esc_html($phone) . '</strong>
            </td></tr>
            <tr><td style="padding:12px 20px; background:#fff; border-bottom:1px solid #e8edf5;">
                <span style="font-size:11px;color:#888;display:block;text-transform:uppercase;letter-spacing:1px;">🗺️ Tour</span>
                <strong style="color:#1a2e4a;">' . esc_html($tour) . '</strong>
            </td></tr>
            <tr><td style="padding:12px 20px; background:#f4f7fb; border-bottom:1px solid #e8edf5;">
                <span style="font-size:11px;color:#888;display:block;text-transform:uppercase;letter-spacing:1px;">📅 Travel Date</span>
                <strong style="color:#1a2e4a;">' . esc_html($travel_date) . '</strong>
            </td></tr>
            <tr><td style="padding:12px 20px; background:#fff; border-bottom:1px solid #e8edf5;">
                <span style="font-size:11px;color:#888;display:block;text-transform:uppercase;letter-spacing:1px;">👥 Travelers</span>
                <strong style="color:#1a2e4a;">' . esc_html($travelers) . '</strong>
            </td></tr>
            <tr><td style="padding:12px 20px; background:#f4f7fb;">
                <span style="font-size:11px;color:#888;display:block;text-transform:uppercase;letter-spacing:1px;">💬 Message</span>
                <span style="color:#1a2e4a;">' . esc_html($message) . '</span>
            </td></tr>
        </table>

        <p style="margin:0; font-size:13px; color:#999; text-align:center; font-style:italic;">
            Log in to your admin dashboard to manage this reservation.
        </p>';

    wp_mail(
        get_option('admin_email'),
        '🌍 New Reservation: ' . $client_name,
        rdt_email_template('New Reservation', $admin_content),
        $headers
    );

    // --- Email Client ---
    if ($client_email) {
        $client_content = '
            <h2 style="margin:0 0 6px; color:#1a2e4a; font-size:22px; font-weight:700;">Thank you, ' . esc_html($first_name) . '! 🎉</h2>
            <p style="margin:0 0 24px; color:#555; font-size:15px; line-height:1.7;">
                We have received your reservation and we\'re thrilled to welcome you!<br>
                Our team will get back to you within <strong style="color:#1a2e4a;">' . RDT_RESPONSE_TIME . '</strong>.
            </p>

            <p style="margin:0 0 8px; color:#1a2e4a; font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:1px;">📋 Your Reservation Summary</p>
            ' . rdt_reservation_summary($travel_date, $travelers, $message) . '

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                <tr>
                    <td style="background:linear-gradient(135deg, #e8621a, #f59b42); border-radius:10px; padding:20px; text-align:center;">
                        <p style="margin:0 0 4px; color:rgba(255,255,255,0.85); font-size:12px; text-transform:uppercase; letter-spacing:2px;">We respond within</p>
                        <p style="margin:0; color:#ffffff; font-size:24px; font-weight:700;">⚡ ' . RDT_RESPONSE_TIME . '</p>
                    </td>
                </tr>
            </table>

            <p style="margin:24px 0 0; color:#888; font-size:14px; text-align:center; line-height:1.7;">
                Can\'t wait to show you the wonders of Madagascar 🌿<br>
                <strong style="color:#e8621a;">— The ' . RDT_COMPANY_NAME . ' Team</strong>
            </p>';

        wp_mail(
            $client_email,
            '✅ Your Reservation Request – ' . RDT_COMPANY_NAME,
            rdt_email_template('Reservation Confirmation', $client_content),
            $headers
        );
    }
}


// ============================================================
// EMAIL 2 : Disponibilité confirmée → Demande d'acompte
// ============================================================
function sendMailAvailabilityConfirmed($post_id, $post) {
    $client_email   = get_post_meta($post_id, 'email', true);
    $first_name     = get_post_meta($post_id, 'first_name', true);
    $travel_date    = get_post_meta($post_id, 'travel_date', true);
    $travelers      = get_post_meta($post_id, 'travelers', true);
    $deposit_number = RDT_COMPANY_PHONE;
    $headers        = ['Content-Type: text/html; charset=UTF-8'];

    if ($client_email) {
        $content = '
            <h2 style="margin:0 0 6px; color:#1a2e4a; font-size:22px; font-weight:700;">Great news, ' . esc_html($first_name) . '! 🎊</h2>
            <p style="margin:0 0 24px; color:#555; font-size:15px; line-height:1.7;">
                Your requested dates are <strong style="color:#1a2e4a;">available</strong>!<br>
                To secure your booking, please send the deposit to the number below.
            </p>

            <p style="margin:0 0 8px; color:#1a2e4a; font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:1px;">📋 Your Reservation</p>
            ' . rdt_reservation_summary($travel_date, $travelers) . '

            <table width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;">
                <tr>
                    <td style="border:2px solid #e8621a; border-radius:10px; padding:24px; text-align:center;">
                        <p style="margin:0 0 8px; font-size:11px; color:#888; text-transform:uppercase; letter-spacing:2px;">Send your deposit to</p>
                        <p style="margin:0 0 8px; font-size:30px; font-weight:700; color:#e8621a; letter-spacing:2px;">' . esc_html($deposit_number) . '</p>
                        <span style="font-size:13px; color:#888; background:#f4f7fb; padding:4px 14px; border-radius:20px;">📱 MVola / Orange Money</span>
                    </td>
                </tr>
            </table>

            <p style="margin:0 0 24px; color:#555; font-size:14px; line-height:1.8;">
                Once we receive your deposit, your reservation will be <strong style="color:#1a2e4a;">fully confirmed</strong>
                and we will send you all the trip details. 🌍
            </p>

            <p style="margin:0; color:#888; font-size:14px; text-align:center; line-height:1.7;">
                Questions? Reply to this email or reach us on WhatsApp.<br>
                <strong style="color:#e8621a;">— The ' . RDT_COMPANY_NAME . ' Team</strong>
            </p>';

        wp_mail(
            $client_email,
            '🌍 Availability Confirmed – Please Send Your Deposit',
            rdt_email_template('Availability Confirmed', $content),
            $headers
        );
    }
}


// ============================================================
// EMAIL 3 : Acompte reçu → Réservation complètement confirmée
// ============================================================
function sendMailDepositReceived($post_id, $post) {
    $client_email = get_post_meta($post_id, 'email', true);
    $first_name   = get_post_meta($post_id, 'first_name', true);
    $travel_date  = get_post_meta($post_id, 'travel_date', true);
    $travelers    = get_post_meta($post_id, 'travelers', true);
    $headers      = ['Content-Type: text/html; charset=UTF-8'];

    if ($client_email) {
        $content = '
            <h2 style="margin:0 0 6px; color:#1a2e4a; font-size:22px; font-weight:700;">You\'re all set, ' . esc_html($first_name) . '! 🥳</h2>
            <p style="margin:0 0 24px; color:#555; font-size:15px; line-height:1.7;">
                We have received your deposit and your reservation is now <strong style="color:#1a2e4a;">fully confirmed</strong>.<br>
                Get ready for an unforgettable adventure in Madagascar!
            </p>

            <p style="margin:0 0 8px; color:#1a2e4a; font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:1px;">📋 Confirmed Reservation</p>
            ' . rdt_reservation_summary($travel_date, $travelers) . '

            <table width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;">
                <tr>
                    <td style="background:linear-gradient(135deg, #1a6b3a, #2e9e57); border-radius:10px; padding:24px; text-align:center;">
                        <p style="margin:0 0 6px; color:rgba(255,255,255,0.8); font-size:11px; text-transform:uppercase; letter-spacing:3px;">Reservation Status</p>
                        <p style="margin:0; color:#ffffff; font-size:26px; font-weight:700;">✅ CONFIRMED</p>
                    </td>
                </tr>
            </table>

            <p style="margin:0 0 24px; color:#555; font-size:14px; line-height:1.8;">
                Our team will be in touch soon with your <strong style="color:#1a2e4a;">full itinerary</strong>,
                meeting point, and everything you need before departure. 🌿
            </p>

            <p style="margin:0; color:#888; font-size:14px; text-align:center; line-height:1.7;">
                See you soon in Madagascar 🌺<br>
                <strong style="color:#e8621a;">— The ' . RDT_COMPANY_NAME . ' Team</strong>
            </p>';

        wp_mail(
            $client_email,
            '🎉 Your Trip is Confirmed – See You in Madagascar!',
            rdt_email_template('Reservation Confirmed', $content),
            $headers
        );
    }
}