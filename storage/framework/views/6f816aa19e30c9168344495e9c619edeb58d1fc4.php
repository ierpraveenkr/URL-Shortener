<?php $__env->startSection('title', 'Accept Invitation - Premium URL Shortener'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center">
    <div class="login-container">
        <div class="text-center mb-8">
            <h1 class="title">Accept Invitation</h1>
            <p style="color: var(--text-muted);">Set your name and password to join <?php echo e($invitation->company->name); ?></p>
        </div>

        <div class="glass-panel">
            <form action="<?php echo e(route('invite', ['token' => $invitation->token])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="text" class="form-control" value="<?php echo e($invitation->email); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" id="name" name="name" class="form-control" required autofocus>
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="error-text"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group mb-6">
                    <label for="password" class="form-label">Choose Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="error-text"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="btn">Complete Registration</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/praveen/Desktop/url_shortner/resources/views/auth/invite.blade.php ENDPATH**/ ?>