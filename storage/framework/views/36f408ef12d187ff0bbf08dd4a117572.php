<?php
    $code = 404;
    $label = 'OFF THE SCOREBOARD';
    $title = 'Looks like this ball went out of play.';
    $message = "We couldn't find the page you're looking for. It may have moved, been removed, or the address may be incorrect.";
    $icon = 'fa-solid fa-magnifying-glass';
?>

<?php echo $__env->make('errors.cricket', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/errors/404.blade.php ENDPATH**/ ?>