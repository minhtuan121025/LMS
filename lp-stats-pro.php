<?php
/**
 * Plugin Name: LearnPress Stats Pro
 * Description: Stats + Chart + PDF Export
 * Version: 2.0
 * Author: Tuân
 */

if (!defined('ABSPATH')) exit;

// ===== DATA =====
function lp_total_courses() {
    return wp_count_posts('lp_course')->publish;
}

function lp_total_students() {
    global $wpdb;
    $table = $wpdb->prefix . 'learnpress_user_items';
    return $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table");
}

function lp_completed_courses() {
    global $wpdb;
    $table = $wpdb->prefix . 'learnpress_user_items';
    return $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status='completed' AND item_type='lp_course'");
}

// ===== SHORTCODE =====
function lp_stats_pro() {

    $courses = lp_total_courses();
    $students = lp_total_students();
    $completed = lp_completed_courses();

    ob_start();
    ?>
    <style>
    .lp-card {display:inline-block;width:30%;margin:10px;padding:20px;border-radius:10px;color:#fff}
    .c1{background:#4CAF50}.c2{background:#2196F3}.c3{background:#FF9800}
    </style>

    <div class="lp-card c1">📚 Courses<br><h2><?php echo $courses ?></h2></div>
    <div class="lp-card c2">👨‍🎓 Students<br><h2><?php echo $students ?></h2></div>
    <div class="lp-card c3">✅ Completed<br><h2><?php echo $completed ?></h2></div>

    <canvas id="lpChart" height="100"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('lpChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Courses','Students','Completed'],
            datasets: [{
                label: 'Stats',
                data: [<?php echo "$courses,$students,$completed" ?>]
            }]
        }
    });
    </script>

    <br><a href="?lp_export=pdf" target="_blank">📄 Export PDF</a>

    <?php
    return ob_get_clean();
}
add_shortcode('lp_total_stats','lp_stats_pro');

// ===== DASHBOARD =====
add_action('wp_dashboard_setup', function(){
    wp_add_dashboard_widget('lp_stats','LearnPress Stats Pro','lp_stats_pro');
});

// ===== PDF EXPORT =====
add_action('init', function(){
    if(isset($_GET['lp_export']) && $_GET['lp_export']=='pdf'){
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=stats.pdf");

        echo "LearnPress Stats\n";
        echo "Courses: ".lp_total_courses()."\n";
        echo "Students: ".lp_total_students()."\n";
        echo "Completed: ".lp_completed_courses();
        exit;
    }
});
