<?php

/**
 * This is the main entry point of the application.
 *
 * Previously, this might have been the login page. Now that the login
 * requirement has been removed, this file simply redirects the user
 * directly to the main dashboard.
 */
header("Location: /maintenance/views/dashboard");
exit;
