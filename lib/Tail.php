<?php
class Tail
{
  protected $path = null;
  protected $first = true;
  protected $pos = 0;

  /**
   * Follow a file
   * @param string $path
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function __construct($path)
  {
    $this->path = $path;
  }

  /**
   * Read a new line a long as it is possible.
   * Goes straight to the end the first time if the file exists.
   * @return mixed a string (including line return), false (EOF) or null (failure)
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function consume()
  {
    $first = $this->first;
    $this->first = false;
    if (!is_readable($this->path))
    {

      return null;
    }
    $fd = fopen($this->path, 'r');
    if ($first)
    {
      fseek($fd, 0, SEEK_END);
    }
    else
    {
      fseek($fd, $this->pos, SEEK_SET);
    }
    $line = fgets($fd);
    $this->pos = ftell($fd);

    if ($line === false)
    {
      /* Detect file truncation.
       * There seem to be no better way, as fseek will accept
       * to go over EOF and fgets will not handle it differently either.
       */
      $stat = fstat($fd);
      if ($stat['size'] < $this->pos)
      {
        // rewind
        $this->pos = 0;
      }
    }
    fclose($fd);

    return $line;
  }
}

class MultiTail
{
  protected $tails = array();

  /**
   * Adds a Tail to watch
   * @param string $name Unique name
   * @param Tail $tail
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function add($name, $tail)
  {
    $this->tails[$name] = $tail;
  }

  /**
   * Calls consume() on every Tail and displays lines.
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function consume()
  {
    foreach ($this->tails as $name => $tail)
    {
      while ($this->display($tail->consume(), $name));
    }
  }

  /**
   * Display a line from a Tail, if it is valid.
   * @param mixed $line
   * @param string $name Unique name of the Tail
   * @return boolean Line validity
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function display($line, $name)
  {
    if (is_string($line))
    {
      echo $name.': '.$line;

      return true;
    }

    return false;
  }
}
