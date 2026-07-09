<?php $__env->startSection('title', 'Dashboard - Premium URL Shortener'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen" style="padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Dashboard</h1>
        <form action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn" style="width: auto; background: var(--error);">Logout</button>
        </form>
    </div>

    <div class="glass-panel mb-8">
        <h2>Your Info</h2>
        <p><strong>Name:</strong> <?php echo e(auth()->user()->name); ?></p>
        <p><strong>Role:</strong> <?php echo e(auth()->user()->role->name ?? 'Unknown'); ?></p>
        <?php if(auth()->user()->company): ?>
            <p><strong>Company:</strong> <?php echo e(auth()->user()->company->name); ?></p>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div style="background: var(--primary); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: white;">
            <?php echo e(session('success')); ?>

            <?php if(session('invite_link')): ?>
                <br><strong>Invite Link:</strong> <a href="<?php echo e(session('invite_link')); ?>" style="color: white; text-decoration: underline;"><?php echo e(session('invite_link')); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div style="background: var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: white;">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <?php if(auth()->user()->role_id !== 1): ?>
        <div class="glass-panel mb-8">
            <h2 style="margin-bottom: 1rem;">Generate Short URL</h2>
            <form action="<?php echo e(route('urls.store')); ?>" method="POST" style="display: flex; gap: 1rem;">
                <?php echo csrf_field(); ?>
                <input type="url" name="original_url" class="form-control" placeholder="e.g. https://example.com/very-long-url" required>
                <button type="submit" class="btn" style="width: auto;">Generate</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if(auth()->user()->role_id === 1 || auth()->user()->role_id === 2): ?>
        <div class="glass-panel mb-8">
            <h2 style="margin-bottom: 1rem;">Invite New <?php echo e(auth()->user()->role_id === 1 ? 'Client Admin' : 'Team Member'); ?></h2>
            <form action="<?php echo e(route('invitations.store')); ?>" method="POST" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <?php echo csrf_field(); ?>
                <?php if(auth()->user()->role_id === 1): ?>
                    <input type="text" name="company_name" class="form-control" placeholder="New Company Name" style="flex: 1; min-width: 200px;" required>
                <?php endif; ?>
                <input type="email" name="email" class="form-control" placeholder="Invite Email" style="flex: 1; min-width: 200px;" required>
                
                <?php if(auth()->user()->role_id === 2): ?>
                    <select name="role_id" class="form-control" style="flex: 1; min-width: 200px;" required>
                        <option value="2">Admin</option>
                        <option value="3">Member</option>
                    </select>
                <?php endif; ?>
                
                <button type="submit" class="btn" style="width: auto;">Send Invitation</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="glass-panel mb-8">
        <h2 style="margin-bottom: 1rem;">Short URLs (<?php echo e($urls->total()); ?>)</h2>
        <ul style="list-style: none; color: var(--text-muted);">
            <?php $__currentLoopData = $urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li style="margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--surface-border); display: flex; justify-content: space-between;">
                    <div>
                        <a href="<?php echo e(url($url->short_code)); ?>" target="_blank" style="color: var(--primary); font-weight: bold; text-decoration: none;"><?php echo e(url($url->short_code)); ?></a> 
                        -> <?php echo e(Str::limit($url->original_url, 50)); ?>

                    </div>
                    <span style="font-size: 0.8rem;">(Company: <?php echo e($url->company->name ?? 'N/A'); ?>, User: <?php echo e($url->user->name ?? 'N/A'); ?>,Clicks:<?php echo e($url->click_count); ?>)</span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($urls->isEmpty()): ?>
                <li>No URLs found.</li>
            <?php endif; ?>
        </ul>
        <div style="margin-top: 1rem;">
            <?php echo e($urls->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/praveen/Desktop/url_shortner/resources/views/dashboard.blade.php ENDPATH**/ ?>