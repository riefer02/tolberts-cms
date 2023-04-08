<?php
/**
 * Template Name: Cron Test Page
 *
 * This template is used to test cron jobs and associated functions.
 */

// Run the cron job function
riefCheckAndSetEventStatus();

// Output a message to indicate that the cron job has run
echo '<p>Cron job has been executed. Check the events post type to see if any posts were updated.</p>';
?>