<?php

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
  set_log('Light level set to ' . $level . '/100');
}

function play_sound() {
  set_log('Starting 5 minutes of alarm sound!');
}

function set_log($msg) {
  echo $msg . '<br />';
}
