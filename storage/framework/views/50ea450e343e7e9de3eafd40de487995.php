<?php
    $code = 500;
    $label = 'PLAY INTERRUPTED';
    $title = 'Something went wrong on our side.';
    $message = 'An unexpected server error stopped us from completing your request. Please try again in a little while.';
    $icon = 'fa-solid fa-screwdriver-wrench';
?>

<?php echo $__env->make('errors.cricket', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/errors/500.blade.php ENDPATH**/ ?>