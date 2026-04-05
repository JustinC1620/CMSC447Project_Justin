<?php
/**
 * Shared tutoring sidebar.
 * Put this in your theme as sidebar-tutoring.php.
 */
?>
<div class="sidebar">
<button class="section-menu-toggle">In this section <span class="icon icon-chevron"></span></button>
<nav><ul class="section-menu"><li class="menu-item menu-item-type-post_type menu-item-object-page current-menu-ancestor current_page_ancestor menu-item-has-children"><a href="https://academicsuccess.umbc.edu/academic-learning-resources/" target="_self">Academic Learning Resources</a>
<ul class="sub-menu">
<li class="menu-item menu-item-type-post_type menu-item-object-page current-menu-ancestor current-menu-parent current_page_parent current_page_ancestor menu-item-has-children"><a href="https://academicsuccess.umbc.edu/tutoring/" target="_self">Tutoring</a>
<ul class="sub-menu">
<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="https://academicsuccess.umbc.edu/appointment-tutoring/" target="_self">Appointment Tutoring</a></li>
<?php
$is_current = is_page('drop-in-tutoring');
$current_classes = $is_current ? ' current-menu-item page_item current_page_item' : '';
?>
<li class="menu-item menu-item-type-post_type menu-item-object-page page-item-527<?php echo $current_classes; ?>">
    <a href="<?php echo esc_url(home_url('/drop-in-tutoring/')); ?>" target="_self">Drop-In Tutoring</a>
</li>
<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="https://academicsuccess.umbc.edu/writing-center/" target="_self">Writing Center</a></li>
<?php 
    $is_current = is_page('tutoring-admin');
    $current_classes = $is_current ? ' current-menu-item page_item current_page_item' : '';
    if (current_user_can('staff_control')) {
        echo '<li class="menu-item menu-item-type-post_type menu-item-object-page ' . $current_classes . '"><a href="' . home_url('/tutoring-admin') . '">Tutoring Admin</a></li>';
    }

?>
</ul></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children"><a href="https://academicsuccess.umbc.edu/si-pass/" target="_self">SI PASS</a>
<ul class="sub-menu">
<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="https://academicsuccess.umbc.edu/si-pass/si-pass-schedule/" target="_self">SI PASS Schedule</a></li>
</ul>
</li>
<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="https://academicsuccess.umbc.edu/student-resources/" target="_self">Student Resources</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="https://academicsuccess.umbc.edu/academic-success-meetings/" target="_self">Academic Skills Meetings</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="https://academicsuccess.umbc.edu/early-academic-alerts/" target="_self">Early Academic Alerts</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="https://academicsuccess.umbc.edu/placement-testing/" target="_self">Placement Testing</a></li>
</ul>
</li>
</ul></nav>
</div>
