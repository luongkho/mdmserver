<?php
/**
 * 
 */
class deployTask extends sfBaseTask
{
  protected function configure()
  {
   
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'mdmserver'),
      // add your own options here
      new sfCommandOption('step', null, sfCommandOption::PARAMETER_REQUIRED, 'The step name', 'init'),

    ));

    $this->namespace        = 'mdm';
    $this->name             = 'deploy';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [deploy|INFO] task does things.
Deploy all changes in 4 steps:

Step 1: drop old mdm database
  [php symfony mdm:deploy --step=drop|INFO]

Step 2: create mdm database
  [php symfony mdm:deploy --step=init|INFO]

Step 3: generate files and tables base on schema.yml
  [php symfony mdm:deploy --step=generate|INFO]

Step 4: load data
  [php symfony mdm:deploy --step=data|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $tasks = array();

    switch ($options['step']) {
      case 'drop':
        $tasks = array(
          'cache:clear',
          'project:permissions',
          'log:clear',
          array('doctrine:drop-db','mdmserver'),
        );
        break;
      case 'init':
        $tasks = array(
          array('doctrine:build-db','mdmserver'),
       );
        break;    
      case 'generate':
        $tasks = array(
          'doctrine:build-model',
          'doctrine:build-forms',
          'doctrine:build-filters',
          'doctrine:build-sql',
          'doctrine:insert-sql'     
       );
        break;  
      case 'data':
        $tasks = array(
          'doctrine:data-load'
       );
        break;  
      default:
        # code...
        break;
    }


    
     
    foreach($tasks as $task)
    {
      if(is_array($task))
      {
        $this->runTask($task[0],$task[1]);  
      }
      else
      {
        $this->runTask($task);  
      }      
    }
  }
}
