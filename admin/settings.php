<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['super_admin', 'admin']);

$db = Database::getConnection();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    $settingsToUpdate = [
        'site_name' => Security::sanitize($_POST['site_name'] ?? ''),
        'division_title' => Security::sanitize($_POST['division_title'] ?? ''),
        'division_motto' => Security::sanitize($_POST['division_motto'] ?? ''),
        'division_statement' => Security::sanitize($_POST['division_statement'] ?? ''),
        'contact_email' => Security::validateEmail($_POST['contact_email'] ?? '') ?: 'lagunawise.ph@gmail.com',
        'organization_location' => Security::sanitize($_POST['organization_location'] ?? ''),
        'social_facebook' => Security::sanitize($_POST['social_facebook'] ?? ''),
        'social_instagram' => Security::sanitize($_POST['social_instagram'] ?? ''),
        'social_twitter' => Security::sanitize($_POST['social_twitter'] ?? ''),
        'social_linkedin' => Security::sanitize($_POST['social_linkedin'] ?? ''),
        'footer_copyright' => Security::sanitize($_POST['footer_copyright'] ?? ''),
        'newsletter_enabled' => isset($_POST['newsletter_enabled']) ? '1' : '0'
    ];

    $updateStmt = $db->prepare("
        INSERT INTO website_settings (setting_key, setting_value, updated_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
    ");

    foreach ($settingsToUpdate as $key => $value) {
        $updateStmt->execute([$key, $value]);
    }

    log_activity((int)$user['id'], 'settings_updated', 'settings', null, "Platform settings updated.");
    flash('success', "Website settings saved successfully.");
    header('Location: ' . url('admin/settings.php'));
    exit;
}

$adminPageTitle = 'Website Configuration Settings';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Configuration</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Platform Settings</h1>
        <p class="text-xs text-slate-500 mt-0.5">Customize division metadata, partnership contacts, location, and global parameters.</p>
    </div>
</div>

<form action="<?= url('admin/settings.php') ?>" method="POST" class="max-w-4xl space-y-8">
    <?= Security::csrfField() ?>

    <!-- Identity Settings -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-5">
        <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-building-columns text-lwblue"></i>
            <span>Division Identity</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Website Title</label>
                <input type="text" name="site_name" value="<?= e(get_setting('site_name')) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Division Subtitle</label>
                <input type="text" name="division_title" value="<?= e(get_setting('division_title')) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Platform Motto / Hero Tagline</label>
            <input type="text" name="division_motto" value="<?= e(get_setting('division_motto')) ?>" 
                   class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Mission Statement</label>
            <textarea name="division_statement" rows="3" 
                      class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"><?= e(get_setting('division_statement')) ?></textarea>
        </div>
    </div>

    <!-- Contact & Location Settings -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-5">
        <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-location-dot text-lwblue"></i>
            <span>Headquarters &amp; Contact</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Partnership Email</label>
                <input type="email" name="contact_email" value="<?= e(get_setting('contact_email')) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Headquarters Location</label>
                <input type="text" name="organization_location" value="<?= e(get_setting('organization_location')) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>
        </div>
    </div>

    <!-- Social Media Links -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-5">
        <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-share-nodes text-lwblue"></i>
            <span>Official Social Channels</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Facebook URL</label>
                <input type="url" name="social_facebook" value="<?= e(get_setting('social_facebook')) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Instagram URL</label>
                <input type="url" name="social_instagram" value="<?= e(get_setting('social_instagram')) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">X / Twitter URL</label>
                <input type="url" name="social_twitter" value="<?= e(get_setting('social_twitter')) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">LinkedIn URL</label>
                <input type="url" name="social_linkedin" value="<?= e(get_setting('social_linkedin')) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>
        </div>
    </div>

    <!-- Footer & Legal -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-5">
        <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-scale-balanced text-lwblue"></i>
            <span>Footer &amp; Legal Notices</span>
        </h2>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Footer Copyright Text</label>
            <input type="text" name="footer_copyright" value="<?= e(get_setting('footer_copyright')) ?>" 
                   class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
        </div>

        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
            <input type="checkbox" name="newsletter_enabled" value="1" <?= get_setting('newsletter_enabled') === '1' ? 'checked' : '' ?> class="rounded text-lwblue focus:ring-lwblue">
            <span>Enable Public Newsletter Subscriptions</span>
        </label>
    </div>

    <button type="submit" class="px-8 py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center gap-2">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>Save All Settings</span>
    </button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
