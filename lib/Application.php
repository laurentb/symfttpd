<?php
abstract class Application
{
  protected $project_path;

  /**
   * @param string $path Project root path. If not provided, try current directory.
   */
  abstract public function __construct($project_path = null);

  /**
   * Get the current project's root path
   * @return string Absolute path
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function getProjectPath()
  {

    return $this->project_path;
  }

  /**
   * Get the command to launch to get the rewriting rules.
   * The PHP executable should be %PHP%, and be replaced at runtime.
   *
   * @return string Command
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  abstract public function getGenconfCmd();
}
