<?php
    $code = 403;
    $label = 'RESTRICTED AREA';
    $title = "You can't enter this part of the ground.";
    $message = "You don't have permission to access this page. If you believe this is a mistake, please contact the site administrator.";
    $icon = 'fa-solid fa-lock';
?>

<?php echo $__env->make('errors.cricket', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/errors/403.blade.php ENDPATH**/ ?>