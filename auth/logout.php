<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

logout_user();

flash('info', 'You have been safely signed out. Thank you for engaging with the LagunaWISE Ideology Division.');
header('Location: ' . url());
exit;
