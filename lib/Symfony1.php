<?php
class Symfony1 extends Application
{
  /**
   * TODO if not in the root of the project, try to find it (like git does)
   * @see Application::__construct
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function __construct($project_path = null)
  {
    if ($project_path === null)
    {
      $project_path == getcwd();
    }
    $project_path = realpath($project_path);
    if (!is_file($project_path.'/symfony'))
    {
      throw new Exception('Not in a symfony project');
    }

    $this->project_path = $project_path;
  }
}
