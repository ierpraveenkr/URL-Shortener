<h1>You're Invited!</h1>
<p>You have been invited to join <strong><?php echo e($invitation->company->name); ?></strong>.</p>
<p>Click the link below to accept your invitation and set up your account:</p>
<a href="<?php echo e(route('invite', ['token' => $invitation->token])); ?>"><?php echo e(route('invite', ['token' => $invitation->token])); ?></a>
<?php /**PATH /Users/praveen/Desktop/url_shortner/resources/views/emails/invite.blade.php ENDPATH**/ ?>