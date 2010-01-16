<?php
class Symfony
{
  /**
   * Get the current project's root path
   * @return string Absolute path
   *
   * TODO if not in the root of the project, try to find it (like git does)
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static public function getProjectPath()
  {
    $project_path = realpath(Argument::get('P', 'path', getcwd()));
    if (!is_file($project_path.'/symfony'))
    {
      throw new Exception('Not in a symfony project');
    }

    return $project_path;
  }
}
