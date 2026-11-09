<?php $__env->startSection('content'); ?>
<?php
    $errorCode = $code ?? 500;
    $errorLabel = $label ?? 'MATCH INTERRUPTED';
    $errorTitle = $title ?? 'Something went wrong';
    $errorMessage = $message ?? 'We could not complete your request.';
    $errorIcon = $icon ?? 'fa-solid fa-triangle-exclamation';
?>

<style>
    .npl-error-page {
        --npl-navy: #101b35;
        --npl-red: #dc2626;
        --npl-muted: #64748b;
        position: relative;
        isolation: isolate;
        overflow: hidden;
        min-height: 570px;
        display: grid;
        place-items: center;
        padding: 64px 20px;
        background: #f5f7fb;
        color: var(--npl-navy);
    }

    .npl-error-page *,
    .npl-error-page *::before,
    .npl-error-page *::after {
        box-sizing: border-box;
    }

    .npl-error-page::before {
        content: "";
        position: absolute;
        z-index: -1;
        width: 440px;
        height: 440px;
        right: -190px;
        top: -200px;
        border: 65px solid rgba(220, 38, 38, .045);
        border-radius: 50%;
    }

    .npl-error-page::after {
        content: "";
        position: absolute;
        z-index: -1;
        width: 310px;
        height: 310px;
        left: -160px;
        bottom: -160px;
        border: 45px solid rgba(16, 27, 53, .045);
        border-radius: 50%;
    }

    .npl-error-card {
        width: 100%;
        max-width: 780px;
        overflow: hidden;
        border: 1px solid #e5eaf2;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(16, 27, 53, .09);
    }

    .npl-error-topline {
        height: 5px;
        background: linear-gradient(90deg, #dc2626 0%, #dc2626 35%, #101b35 35%, #101b35 100%);
    }

    .npl-error-body {
        padding: clamp(28px, 6vw, 58px);
        text-align: center;
    }

    .npl-error-brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 32px;
        color: var(--npl-navy);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .npl-error-brand-mark {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 12px;
        background: var(--npl-navy);
        color: #fff;
        font-size: 17px;
    }

    .npl-error-art {
        position: relative;
        display: grid;
        width: 118px;
        height: 118px;
        margin: 0 auto 24px;
        place-items: center;
        border: 1px solid #e7ebf2;
        border-radius: 50%;
        background: radial-gradient(circle, #fff 20%, #f1f4f9 100%);
    }

    .npl-error-art::before,
    .npl-error-art::after {
        content: "";
        position: absolute;
        border: 1px dashed #cbd5e1;
        border-radius: 50%;
    }

    .npl-error-art::before {
        inset: -10px;
    }

    .npl-error-art::after {
        inset: 9px;
        border-style: solid;
        border-color: #e2e8f0;
    }

    .npl-error-art i {
        position: relative;
        z-index: 1;
        color: var(--npl-red);
        font-size: 39px;
    }

    .npl-error-code {
        margin: 0;
        color: var(--npl-navy);
        font-size: clamp(76px, 15vw, 126px);
        font-weight: 900;
        letter-spacing: -.075em;
        line-height: .95;
    }

    .npl-error-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 22px 0 13px;
        padding: 8px 12px;
        border: 1px solid #fee2e2;
        border-radius: 999px;
        background: #fff7f7;
        color: #b91c1c;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .13em;
    }

    .npl-error-title {
        margin: 0;
        color: var(--npl-navy);
        font-size: clamp(24px, 4vw, 34px);
        font-weight: 800;
        letter-spacing: -.035em;
        line-height: 1.2;
    }

    .npl-error-message {
        max-width: 500px;
        margin: 14px auto 0;
        color: var(--npl-muted);
        font-size: 15px;
        line-height: 1.8;
    }

    .npl-error-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
        margin-top: 30px;
    }

    .npl-error-btn {
        display: inline-flex;
        min-height: 46px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 12px 19px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 750;
        text-decoration: none !important;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .npl-error-btn:hover {
        transform: translateY(-2px);
    }

    .npl-error-btn-primary {
        background: var(--npl-navy);
        color: #fff !important;
        box-shadow: 0 7px 18px rgba(16, 27, 53, .15);
    }

    .npl-error-btn-primary:hover {
        background: #1d2b4b;
    }

    .npl-error-btn-secondary {
        border-color: #dce3ed;
        background: #fff;
        color: var(--npl-navy) !important;
    }

    .npl-error-btn-secondary:hover {
        background: #f8fafc;
    }

    .npl-error-divider {
        height: 1px;
        margin: 32px 0 19px;
        background: #edf0f5;
    }

    .npl-error-footnote {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 600;
    }

    .npl-error-footnote i {
        color: #16a34a;
    }

    @media (max-width: 520px) {
        .npl-error-page {
            min-height: 480px;
            padding: 32px 14px;
        }

        .npl-error-card {
            border-radius: 16px;
        }

        .npl-error-body {
            padding: 32px 20px;
        }

        .npl-error-brand {
            margin-bottom: 25px;
        }

        .npl-error-actions {
            flex-direction: column;
        }

        .npl-error-btn {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .npl-error-btn {
            transition: none;
        }
    }
</style>

<section class="npl-error-page">
    <div class="npl-error-card">
        <div class="npl-error-topline"></div>

        <div class="npl-error-body">
            <div class="npl-error-brand">
                <span class="npl-error-brand-mark">
                    <i class="fa-solid fa-baseball-bat-ball" aria-hidden="true"></i>
                </span>
                <span>NPL Season Hub</span>
            </div>

            <div class="npl-error-art" aria-hidden="true">
                <i class="<?php echo e($errorIcon); ?>"></i>
            </div>

            <p class="npl-error-code"><?php echo e($errorCode); ?></p>

            <div class="npl-error-label">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <?php echo e($errorLabel); ?>

            </div>

            <h1 class="npl-error-title"><?php echo e($errorTitle); ?></h1>

            <p class="npl-error-message"><?php echo e($errorMessage); ?></p>

            <div class="npl-error-actions">
                <a class="npl-error-btn npl-error-btn-primary"
                   href="<?php echo e(url('/')); ?>">
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                    Back to Home
                </a>

                <a class="npl-error-btn npl-error-btn-secondary"
                   href="<?php echo e(url('/fixtures')); ?>">
                    <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                    View Fixtures
                </a>
            </div>

            <div class="npl-error-divider"></div>

            <div class="npl-error-footnote">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                <span>NPL Season Hub</span>
                <span aria-hidden="true">·</span>
                <span>Every match. Every moment.</span>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/errors/cricket.blade.php ENDPATH**/ ?>