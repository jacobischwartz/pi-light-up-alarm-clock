<?php

define('DIMMER_PIN', 1);
define('RAMPUP_MINUTES', 30);
define('POSTALARM_MINUTES', 5);
define('USING_TRANSISTOR', TRUE);

date_default_timezone_set('America/Chicago');

/**
 * @param array|stdClass $data A single data structure. Can be deep.
 * @throws Exception
 */
function write_data_file($filename, $data) {
  $dest_folder = realpath('./');
  $filepath = $dest_folder . '/' . $filename;
  $handle = fopen( $filepath, "w");
  if(FALSE === $handle) throw new Exception('Could not open config file here: ' . $filepath . '. Could be a permissions issue.');
  $timestamp = time();
  $wrote = fwrite($handle, json_encode($data));
  if(FALSE === $wrote) throw new Exception('Could not write to config file here: ' . $filepath . '. Could be a permissions issue.');
  fclose($handle);
  chmod($filepath, 0750);
}

/**
 * @return array|null
 */
function get_data_file($filename) {
  if(!file_exists($filename)) return NULL;
  return json_decode(file_get_contents($filename), TRUE);
}

/**
 * @param array|stdClass $data A single data structure. Can be deep.
 * @throws Exception
 */
function write_config($data) {
  write_data_file('config.json', $data);
}

/**
 * @return array|null
 */
function read_config() {
  return get_data_file('config.json');
}

/**
 * @param $data
 * @throws Exception
 */
function write_status($data) {
  write_data_file('status.json', $data);
}

/**
 * @return array|null
 */
function read_status() {
  return get_data_file('status.json');
}

/**
 * @param int $level 0 - 100 where 0 is off and 100 is full on.
 */
function set_light_level($level) {
  $level *= 1023/100;
  $level = intval($level);
  $level = max(0, min($level, 1023));
  set_log('Light level set to ' . $level . '/1023');
  static $gpio_version;
  if(empty($gpio_version)) $gpio_version = shell_exec("gpio -v 2>&1");
  if(FALSE !== stripos($gpio_version, 'command not found')) {
    set_log('No GPIO found');
    return;
  }
  if(USING_TRANSISTOR) $level = 1023 - $level;
  if((1023 === $level) && USING_TRANSISTOR) {
    shell_exec("gpio mode " . DIMMER_PIN . " out 2>&1; gpio write " . DIMMER_PIN . " 1 2>&1");
  } else {
    shell_exec("gpio mode " . DIMMER_PIN . " pwm 2>&1; gpio pwm " . DIMMER_PIN . " " . $level . " 2>&1");
  }
}

function play_sound() {
  set_log('Starting 5 minutes of alarm action!');
  $status = read_status();
  if(NULL === $status) {
    set_log('Could not read status. Aborting alarm action.');
    return;
  }
  if(!empty($status['alarm_in_progress'])) {
    set_log('Tried to start alarm action but already running.');
    return;
  }
  $status['alarm_in_progress'] = TRUE;
  try {
    write_status($status);
  } catch (Exception $e) {
    set_log('Status is not savable. Aborting alarm action. ' . $e->getMessage());
    die();
  }
  // TODO: An audio thing on the rPi.

  /*
   * Mess with the light pattern for a little while.
   * Every 30 seconds for 5 minutes (10 times total):
   * - 3 times (6 seconds total): Ramp on-off over a second then off-on over a second.
   */
  $second_in_microseconds = 1000000;
  $frames_per_second = 20;
  $frame_delay = $second_in_microseconds/$frames_per_second;
  $change_per_frame = 10;
  for($i=0; $i<10; $i++) {

    $light_level = 100;
    set_light_level($light_level);
    for($j=0; $j<3; $j++) {
      while($light_level > 0) {
        $light_level -= $change_per_frame;
        set_light_level($light_level);
        usleep($frame_delay);
      }
      while($light_level < 100) {
        $light_level += $change_per_frame;
        set_light_level($light_level);
        usleep($frame_delay);
      }
    }
    $light_level = 100;
    set_light_level($light_level);
    sleep(24);

  }

  $status = read_status();
  if(NULL === $status) {
    set_log('Could not read status. Finished alarm action but trouble is brewing.');
    return;
  }
  $status['alarm_in_progress'] = FALSE;
  try {
    write_status($status);
  } catch (Exception $e) {
    set_log('Status is not savable. Finished alarm action but trouble is brewing. ' . $e->getMessage());
    return;
  }

}

function set_log($msg) {
  echo $msg . '<br />' . PHP_EOL;
}
