<?php

global $wpdb;
$tablename_create_user_entries = $wpdb->prefix . 'contest_gal1ery_create_user_entries';
$tablename_mails = $wpdb->prefix . "contest_gal1ery_mails";

$start = 0;
$step  = 50;

// --- START ---
if (isset($_GET['start'])) {
    $muster = '/^[0-9]+$/';
    $start_value = isset($_GET['start']) ? $_GET['start'] : '';
    $start = (preg_match($muster, $start_value) === 1) ? absint($_GET['start']) : 0;
}

// --- STEP --- (bugfix: was checking start)
if (isset($_GET['step'])) {
    $muster = '/^[0-9]+$/';
    $step_value = isset($_GET['step']) ? $_GET['step'] : '';
    $step = (preg_match($muster, $step_value) === 1) ? absint($_GET['step']) : 50;
}

// --- SEARCH ---
$cgUserNameEmail = '';
if (isset($_POST['cg-search-user-name-email'])) {
    $cgUserNameEmail = sanitize_text_field(wp_unslash($_POST['cg-search-user-name-email']));
} elseif (isset($_GET['cg-search-user-name-email'])) {
    $cgUserNameEmail = sanitize_text_field(wp_unslash($_GET['cg-search-user-name-email']));
}

if (!empty($cgUserNameEmail)) {
    // 🔍 Search mode — search all three types, but only those not existing in wp_users
    $sql = $wpdb->prepare("
    SELECT Field_Type, Field_Content, Tstamp
    FROM (
        SELECT *
        FROM {$tablename_create_user_entries}
        WHERE (
            (Field_Type = 'main-mail'
             AND Field_Content LIKE %s
             AND Field_Content NOT IN (SELECT user_email FROM {$wpdb->users}))
          OR
            (Field_Type = 'unconfirmed-mail'
             AND Field_Content LIKE %s)
        )
        ORDER BY Tstamp DESC
    ) AS t
    GROUP BY Field_Content
    ORDER BY Tstamp DESC
    LIMIT $start, $step
",
        '%' . $cgUserNameEmail . '%',  // for first LIKE
        '%' . $cgUserNameEmail . '%',  // for second LIKE
    );
    $users = $wpdb->get_results($sql);
    $sqlCount = $wpdb->prepare("
        SELECT COUNT(*) AS total
        FROM (
            SELECT Field_Content 
            FROM {$tablename_create_user_entries}
            WHERE (
                (Field_Type = 'main-mail'
                 AND Field_Content LIKE %s
                 AND Field_Content NOT IN (SELECT user_email FROM {$wpdb->users}))
              OR
                (Field_Type = 'unconfirmed-mail'
                 AND Field_Content LIKE %s)
            )
            GROUP BY Field_Content
        ) AS grouped
    ",
        '%' . $cgUserNameEmail . '%',  // for first LIKE
        '%' . $cgUserNameEmail . '%'   // for second LIKE
    );
    $rows = $wpdb->get_var($sqlCount);
} else {
    // 📧 Default mode — latest mail not in wp_users
    /*$sql = $wpdb->prepare("
        SELECT Field_Type, Field_Content, Tstamp
        FROM {$tablename_create_user_entries}
        WHERE (Field_Type = %s 
          AND Field_Content NOT IN (SELECT user_email FROM {$wpdb->users}))
           OR (Field_Type = %s 
          AND Field_Content IN (SELECT user_email FROM {$wpdb->users}))
        ORDER BY Tstamp DESC LIMIT $start, $step
    ", 'main-mail','unconfirmed-mail');*/
    $sql = $wpdb->prepare("
    SELECT Field_Type, Field_Content, MAX(Tstamp) AS Tstamp
    FROM {$tablename_create_user_entries}
    WHERE (Field_Type = %s 
        AND Field_Content NOT IN (SELECT user_email FROM {$wpdb->users}))
       OR (Field_Type = %s 
        AND Field_Content IN (SELECT user_email FROM {$wpdb->users}))
    GROUP BY Field_Type, Field_Content
    ORDER BY Tstamp DESC
    LIMIT %d, %d
", 'main-mail', 'unconfirmed-mail', $start, $step);
    $users = $wpdb->get_results($sql);
    /*$sqlCount = $wpdb->prepare("
        SELECT COUNT(*) AS total 
        FROM {$tablename_create_user_entries}
        WHERE (Field_Type = %s 
          AND Field_Content NOT IN (SELECT user_email FROM {$wpdb->users}))
           OR (Field_Type = %s 
          AND Field_Content IN (SELECT user_email FROM {$wpdb->users}))
    ", 'main-mail','unconfirmed-mail');*/
    $sqlCount = $wpdb->prepare("
    SELECT COUNT(*) 
    FROM (
        SELECT Field_Type, Field_Content
        FROM {$tablename_create_user_entries}
        WHERE (Field_Type = %s 
            AND Field_Content NOT IN (SELECT user_email FROM {$wpdb->users}))
           OR (Field_Type = %s 
            AND Field_Content IN (SELECT user_email FROM {$wpdb->users}))
        GROUP BY Field_Type, Field_Content
    ) AS t
", 'main-mail', 'unconfirmed-mail');
    $rows = $wpdb->get_var($sqlCount);
}

$keyed = [];

if($rows){
// Extract emails
    $mails = wp_list_pluck($users, 'Field_Content');
// Build placeholders
    $placeholders = implode(',', array_fill(0, count($mails), '%s'));
// Create final query
    $sql2 = $wpdb->prepare("
    SELECT ReceiverMail, COUNT(*) AS count
    FROM {$tablename_mails}
    WHERE ReceiverMail IN ($placeholders)
    GROUP BY ReceiverMail
", $mails);
    $counts = $wpdb->get_results($sql2);
    foreach ($counts as $row) {
        $keyed[$row->ReceiverMail] = (int) $row->count;
    }
}

$cgUserNameEmailGetParam = '&cg-search-user-name-email=' . rawurlencode($cgUserNameEmail);

// --- PAGINATION ---
$nr1           = $start + 1;
$total         = (int) $rows;
$current_start = $start + 1;
$current_end   = min($start + $step, $total);
$prev_start    = max(0, $start - $step);
$next_start    = ($current_end < $total) ? $start + $step : $start;

$baseUrl = "?page=" . cg_get_version() . "/index.php&unconfirmed_management=true{$cgUserNameEmailGetParam}";

// --- OUTPUT ---
echo "
    <div id='cgUnconfirmedListStepsContainer' class='cg_steps_container' style='display:flex;align-items:center;gap:6px;'>
        <div id='cgUnconfirmedUserStepsSelectTitle' class='cg_steps_container_title'>
            Unconfirmed users
        </div>
        <select id='cgUnconfirmedUserStepsSelect' class='" . (($rows == 0) ? 'cg_hidden' : '') . "'>";
for ($i = 0; $i < $rows; $i += $step) {
    $anf      = $i + 1;
    $end      = min($i + $step, $rows);
    $label    = ($anf === $end) ? (string) $end : "$anf-$end";
    $selected = ($anf === $nr1) ? ' selected' : '';

    $href = "?page=" . cg_get_version() . "/index.php"
        . "&step={$step}"
        . "&start={$i}"
        . "&unconfirmed_management=true"
        . $cgUserNameEmailGetParam;

    echo "
                <option value='{$i}' data-cg-href='{$href}'{$selected}>
                    {$label}
                </option>";
}
echo "
        </select>
        
        <div id='cgUnconfirmedUsersStepNav'  class='cg_step_nav " . (($rows == 0) ? 'cg_hidden' : '') . "' style='display:inline-flex;align-items:center;gap:6px;'>
            <a href='" . ($start > 0 ? $baseUrl . "&start={$prev_start}" : '#') . "' class='" . ($start > 0 ? "" : 'cg_disabled_background_opacity') . "'>
                <svg width='14' height='14' viewBox='0 0 16 16' xmlns='http://www.w3.org/2000/svg'>
                    <path d='M10 3 L5 8 L10 13' stroke='currentColor' stroke-width='2' fill='none' stroke-linecap='round' stroke-linejoin='round'/>
                </svg>
            </a>
            <span style='white-space:nowrap;'>
                &nbsp;{$current_start}-{$current_end}&nbsp;of&nbsp;{$total}&nbsp;
            </span>
            <a href='" . ($current_end < $total ? $baseUrl . "&start={$next_start}" : '#') . "' class='" . ($current_end < $total ? "" : 'cg_disabled_background_opacity') . "'>
                <svg width='14' height='14' viewBox='0 0 16 16' xmlns='http://www.w3.org/2000/svg'>
                    <path d='M6 3 L11 8 L6 13' stroke='currentColor' stroke-width='2' fill='none' stroke-linecap='round' stroke-linejoin='round'/>
                </svg>
            </a>
        </div>
        
        <div id='cgUnconfrmedSearch' class='" . (($rows == 0) ? 'cg_hidden' : '') . "'>
            <form id='cgUnconfirmedManagementForm' method='POST' action='?page=" . cg_get_version() . "/index.php&unconfirmed_management=true#cg-search-results-container' class='cg_load_backend_submit'>
                <input 
                    style='flex-grow:1;' 
                    class='cg_search_user_name' 
                    type='text' 
                    placeholder='Exact email'  
                    name='cg-search-user-name-email' 
                    value='" . esc_attr($cgUserNameEmail) . "' 
                />
                <button 
                    type='submit' 
                    id='cgUnconfirmedSearchSubmit' 
                    class='cg_backend_button_gallery_action' 
                    style='height:28px;'
                >
                    Search
                </button>
            </form>
            <div id='cgUnconfirmedSearchReset' title='Refresh and reset search'></div>
        </div>
    </div>
</div>";

echo '
<div id="cgUnconfirmedManagementList" class="cg_table_list">
    <table class="wp-list-table widefat fixed striped users">
        <thead>
            <tr>
                <th scope="col" class="manage-column">Email</th>
                <th scope="col" class="manage-column">Registry date</th>
                <th scope="col" class="manage-column">Action</th>
            </tr>
        </thead>
        <tbody >';
        if(count($users)){
            foreach ($users as $user) {
                echo '<tr>';
                if($user->Field_Type == 'main-mail'){
                    echo '<td><span class="cg_mail_data">'.$user->Field_Content.'</span></td>';
                }
                if($user->Field_Type == 'unconfirmed-mail'){
                    echo '<td><span class="cg_mail_data">'.$user->Field_Content.'</span><br><b class="cg_registered_not_confirmed">Registered but not confirmed</b></td>';
                }
                $regTime = cg_get_time_based_on_wp_timezone_conf($user->Tstamp,'d-M-Y H:i:s');
                echo '<td>'.$regTime.'</td>';
                if(!isset($keyed[$user->Field_Content])){
                    $cg_hide_mails = 'cg_hide';
                    $mailsCount = 0;
                }else{
                    $cg_hide_mails = '';
                    $mailsCount = $keyed[$user->Field_Content];
                }
                echo '<td><div class="cg_resend '.$cgProFalse.'">Resend mail</div><div class="cg_mails '.$cg_hide_mails.'">Mails <b>'.$mailsCount.'</b></div><div class="cg_delete">Delete</div></td>
                </tr>';
            }
        }else{
            echo '<tr>
                        <td colspan="3">No unconfirmed users</td>
                </tr>';
        }
echo     '</tbody>
    </table>
</div>';

