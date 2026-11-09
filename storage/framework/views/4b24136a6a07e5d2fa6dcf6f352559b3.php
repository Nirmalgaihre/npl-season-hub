<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php $__currentLoopData = $urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e($item['loc']); ?></loc>
        <?php if(!empty($item['lastmod'])): ?>
            <lastmod><?php echo e($item['lastmod']); ?></lastmod>
        <?php endif; ?>
    </url>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</urlset>
<?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/seo/sitemap.blade.php ENDPATH**/ ?>