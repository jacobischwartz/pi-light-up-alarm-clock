<?php

require_once 'functions.php';
$now = time();

/* Look up today's settings.
 * If the alarm should be active and we're within range of the alarm time, output the alarm.
 * Otherwise turn it off.
 *
 * Alarm playbook:
 * - 30mins before alarm to time of alarm: Gradually raise light level.
 * - Alarm time: Play a sound that lasts 5 minutes.
 * - 5 minutes after alarm time: Turn off light entirely.
 * - Done.
 */

$config = read_config();
$status = read_status();

if(NULL === $config) {
  set_light_level(0);
  set_log('Cannot run. No configuration available.');
  die();
}

$config_day = strtolower(date('l'));
if(empty($config[$config_day])) {
  set_light_level(0);
  set_log('Cannot run. No configuration for today available.');
  die();
}
if(empty($config[$config_day]['active'])) {
  set_light_level(0);
  set_log('Alarm is set to be inactive today.');
  die();
}

$alarm_time_today = mktime(
    $config[$config_day]['hour'],
    $config[$config_day]['minute'],
    0,
    date('n'),
    date('j'),
    date('Y')
);

set_log('Alarm time for today is ' . date('Y-m-d H:i', $alarm_time_today) . '. Current time is ' . date('Y-m-d H:i', $now));

$alarm_light_ramp_time_start = strtotime('-' . RAMPUP_MINUTES . ' minutes', $alarm_time_today);
$alarm_light_ramp_time_end = $alarm_time_today;
$alarm_sound_time_start = $alarm_time_today;
$alarm_sound_time_end = strtotime('+' . POSTALARM_MINUTES . ' minutes', $alarm_time_today);

if($now < $alarm_light_ramp_time_start) {
  set_light_level(0);
  set_log('Too early to start lighting for today.');
  die();
}
if($now > $alarm_sound_time_end) {
  set_light_level(0);
  set_log('Alarm time has passed for today.');
  die();
}

if($now < $alarm_light_ramp_time_end) {
  $light_ramp_duration = $alarm_light_ramp_time_end - $alarm_light_ramp_time_start;
  $light_ramp_position = $now - $alarm_light_ramp_time_start;
  $target_light_ramp_level = intval(100.0 * $light_ramp_position / $light_ramp_duration);
  set_light_level($target_light_ramp_level);
  set_log('Light raising in progress.');
  die();
}

set_log('Alarm action should be playing.');

if((NULL !== $status) && (empty($status['sound_played']) || ($status['sound_played'] !== $alarm_sound_time_start))) {
  try {
    write_status(array('sound_played' => $alarm_sound_time_start));
    play_sound();
  } catch (Exception $e) {
    set_log('Cannot play alarm sound because status is not savable. ' . $e->getMessage());
    die();
  }
}
