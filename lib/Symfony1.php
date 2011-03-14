<?php
class Symfony1 extends Application
{
  protected $version;

  /**
   * TODO if not in the root of the project, try to find it (like git does)
   * @param string $path Project root path. If not provided, try current directory.
   * @param string $version Symfony version: '1.0', '1.1'…
   * @see Application::__construct
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function __construct($project_path = null, $version)
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
    $this->version = $version;
  }

  /**
   * Find plugins with a "web" directory
   * @param string $project_path
   * @return array Plugin names
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function findPlugins()
  {
    $plugins = array();
    foreach (new DirectoryIterator($this->getProjectPath()."/plugins") as $file)
    {
      $name = $file->getFilename();
      if ($file->isDir()
          && preg_match('/^[^\.].+Plugin$/', $name)
          && is_dir($this->getProjectPath().'/plugins/'.$name.'/web')
      )
      {
        $plugins[] = $name;
      }
    }

    return $plugins;
  }

  /**
   * @return array
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function getAvailableLinks()
  {

    return array(
      'symfony_symlink' => '',
      'lib_symlink' => 'lib',
      'data_symlink' => 'data',
      'web_symlink' => 'data/web/sf',
    );
  }

  /**
   * @return array Filenames of the apps, e.g. array('frontend.php', 'backend.php')
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  public function getApps()
  {
    $applications = array();
    foreach (new DirectoryIterator($this->project_path.'/web') as $file)
    {
      if ($file->isFile() && preg_match('/\.php$/', $file->getFilename()))
      {
        $apps[] = $file->getFilename();
      }
    }
    sort($apps);

    return $apps;
  }
}
