<?php $__env->startSection('title', 'Login - Premium URL Shortener'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center">
    <div class="login-container">
        <div class="text-center mb-8">
            <h1 class="title">URL Shortener</h1>
            <p style="color: var(--text-muted);">Sign in to your account</p>
        </div>

        <div class="glass-panel">
            <form action="<?php echo e(route('login')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="admin@example.com" value="<?php echo e(old('email')); ?>" required autofocus>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error-text"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group mb-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn">Sign In</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/praveen/Desktop/url_shortner/resources/views/auth/login.blade.php ENDPATH**/ ?>