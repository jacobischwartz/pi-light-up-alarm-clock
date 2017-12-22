<?php

define('DIMMER_PIN', 1);
define('RAMPUP_MINUTES', 30);
define('POSTALARM_MINUTES', 5);

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
  $level *= 1023;
  $level = intval($level);
  $level = max(0, min($level, 1023));
  set_log('Light level set to ' . $level . '/100');
  $gpio_version = shell_exec("gpio -v");
  if(FALSE === stripos($gpio_version, 'command not found')) {
    set_log('No GPIO found');
    return;
  }
  if($level < 100) {
    shell_exec("gpio mode 1 pwm; gpio pwm " . DIMMER_PIN . " " . $level);
  } else {
    shell_exec("gpio mode 1 pwm; gpio pwm " . DIMMER_PIN . " " . $level);
  }
}

function play_sound() {
  set_log('Starting 5 minutes of alarm action!');
  // TODO: An audio thing on the rPi.

  /*
   * Mess with the light pattern for a little while.
   * Every 30 seconds for 5 minutes (10 times total):
   * - 3 times (6 seconds total): Ramp on-off over a second then off-on over a second.
   */
  for($i=0; $i<10; $i++) {

    $light_level = 100;
    set_light_level($light_level);
    for($j=0; $j<3; $j++) {
      while($light_level > 5) {
        $light_level -= 1.6;
        set_light_level($light_level);
        usleep(1000000/10);
      }
      while($light_level < 95) {
        $light_level += 1.6;
        set_light_level($light_level);
        usleep(1000000/10);
      }
    }
    $light_level = 100;
    set_light_level($light_level);
    sleep(24);

  }

}

function set_log($msg) {
  echo $msg . '<br />';
}
