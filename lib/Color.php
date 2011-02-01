<?php
/*
 * No "get" prefix on the class methods as pretty much
 * all of them would have it.
 */
class Color
{
  protected static $enabled = false;

  protected static $fgcolors = array(
    'black'  => 30,
    'blue'   => 34,
    'brown'  => 33,
    'cyan'   => 36,
    'green'  => 32,
    'grey'   => 37,
    'purple' => 35,
    'red'    => 31,
    'yellow' => 33,
  );

  protected static $styles = array(
    'normal'    => 0, // also "reset"
    'bright'    => 1, // also "bold"
    'underline' => 4,
    'blink'     => 5,
    'inverse'   => 6, // also "reverse"
  );

  protected static $bgcolors = array(
    'black'  => 40,
    'blue'   => 44,
    'brown'  => 43,
    'cyan'   => 46,
    'green'  => 42,
    'grey'   => 47,
    'purple' => 45,
    'red'    => 41,
    'yellow' => 43,
  );

  /**
   * Get a control code for UNIX-like terminals, if color support is enabled.
   * @param integer $number
   * @return string
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public static function controlCode($number)
  {

    return self::$enabled ? "\033[".$number.'m' : '';
  }

  /**
   * Get a control number from its type and name.
   *
   * @param array $list Type, e.g. Color::$fgcolor
   * @param string $name Name, e.g. 'red'
   * @return integer Control number, e.g. 31
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  protected static function numberFromList($list, $name)
  {

    return $list[$name];
  }

  /**
   * Get the control code for a foreground color.
   * @param string $color Color name, e.g. 'red'
   * @return string Escape sequence
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public static function fgColor($name)
  {

    return self::controlCode(self::numberFromList(self::$fgcolors, $name));
  }

  /**
   * Get the control code for a background color.
   * @param string $color Color name, e.g. 'red'
   * @return string Escape sequence
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public static function bgColor($name)
  {

    return self::controlCode(self::numberFromList(self::$bgcolors, $name));
  }

  /**
   * Get the control code for a style.
   * @param string $color Color name, e.g. 'bold'
   * @return string Escape sequence for this color
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public static function style($name)
  {

    return self::controlCode(self::numberFromList(self::$styles, $name));
  }

  /**
   * Enable color support.
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public static function enable()
  {
    self::$enabled = true;
  }

  /**
   * Disable color support.
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public static function disable()
  {
    self::$enabled = false;
  }
}
