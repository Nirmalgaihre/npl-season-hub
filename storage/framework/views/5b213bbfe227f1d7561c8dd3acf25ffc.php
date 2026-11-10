<?php
    $code = 419;
    $label = 'SESSION EXPIRED';
    $title = 'Your session has timed out.';
    $message = 'This page has been inactive for too long. Return to the previous page and try your action again.';
    $icon = 'fa-solid fa-clock';
?>

<?php echo $__env->make('errors.cricket', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/errors/419.blade.php ENDPATH**/ ?>