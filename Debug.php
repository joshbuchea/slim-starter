<?php

/**
 * Debug Class
 */
class Debug {

  /**
   * Provide debug utility functions
   */
  public static function debug_log( $data ) {
    if ( is_array( $data ) ) {
      $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    }
    else {
      $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";
    }
    echo $output;
  }

  public static function console_log($data) {
    $output = '<script>';
    $output .= 'console.log('. json_encode( $data ) .')';
    $output .= '</script>';
    echo $output;
  }

}
