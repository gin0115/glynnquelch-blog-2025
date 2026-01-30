<?php

namespace SaggyPants;

class Scheduler {

	const HOOK = 'saggy_pants_update_jukebox';

	public static function init(): void {
		// Register the action hook
		add_action( self::HOOK, array( JukeboxUpdater::class, 'update' ) );

		// Schedule daily at midnight if not already scheduled
		add_action( 'init', array( self::class, 'scheduleDaily' ) );
	}

	public static function scheduleDaily(): void {
		// Only schedule if Action Scheduler is available
		if ( ! function_exists( 'as_next_scheduled_action' ) ) {
			return;
		}

		// Check if already scheduled
		if ( as_next_scheduled_action( self::HOOK ) !== false ) {
			return;
		}

		// Schedule for midnight tonight
		$midnight = strtotime( 'tomorrow midnight' );

		as_schedule_recurring_action(
			$midnight,
			DAY_IN_SECONDS,
			self::HOOK,
			array(),
			'saggy-pants'
		);
	}

	public static function unschedule(): void {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::HOOK );
		}
	}
}

