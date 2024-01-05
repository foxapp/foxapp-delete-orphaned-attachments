<?php
/**
 * @link https://plugins.foxapp.net/
 * Plugin Name: FoxApp - Delete Orphaned Attachments
 * Plugin URI: https://plugins.foxapp.net/
 * Description: Deletes orphaned attachments not referenced in any post or page content.
 * Version: 1.1.0
 * Author: FoxApp
 * Author URI: https://plugins.foxapp.net/
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: foxapp-delete-orphaned-attachments
 * Domain Path: /languages
 **/

function foxapp_delete_orphaned_attachments() {
	$orphaned_attachments = get_posts( array(
		'post_type'      => 'attachment',
		'posts_per_page' => - 1,
		'post_parent'    => 0,
	) );

	foreach ( $orphaned_attachments as $attachment ) {
		$attachment_id = $attachment->ID;

		$attachment_in_content = get_posts( array(
			'post_type'      => 'any',
			'posts_per_page' => - 1,
			's'              => wp_get_attachment_url($attachment_id),
		) );

		$elementor_templates = get_posts(array(
			'post_type'      => 'elementor_library', // Elementor templates
			'posts_per_page' => -1,
			's'              => wp_get_attachment_url($attachment_id),
		));

		if (empty($attachment_in_content) && empty($elementor_templates)) {
			wp_delete_post($attachment_id, true);
		}
	}
}

function schedule_foxapp_delete_orphaned_attachments() {
	if ( ! wp_next_scheduled( 'foxapp_delete_orphaned_attachments_event' ) ) {
		wp_schedule_event( time(), 'daily', 'foxapp_delete_orphaned_attachments_event' );
	}
}

add_action( 'foxapp_delete_orphaned_attachments_event', 'foxapp_delete_orphaned_attachments' );

register_activation_hook( __FILE__, 'schedule_foxapp_delete_orphaned_attachments' );

register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'foxapp_delete_orphaned_attachments_event' );
} );
