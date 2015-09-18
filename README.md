# Phalcon-generic-repository
Generic repository for Phalcon-framework

Move the folder named 'repository 'somewhere in your project.In my case it will be vendor.
Then, at your services file: 
  	   
  	        /**
             * Autoload repository
             */
            include __DIR__ . "/../../vendor/repository/autoload.php";
            
            
	        /**
             * Generic Repository
             */
            $dependencyInjector['repository'] = new GenericRepository($config, 'user');
            
At some Controller :

    /**
     * List all created pages
     *
     * @link   /page        - method GET
     * @link   /page/1      - method GET
     */
    public function indexAction($id)
    {
        if (!empty($id)) {
            echo $this->repository->setModel('Page')->setCriteria(array("id = '$id'"))
                ->mergeResults()
                ->findFirst()
                ->getRelated(array('articles', 'comments'))
                ->returnAs('json');
            exit;
        }
        echo $this->repository->setModel('Page')
            ->mergeResults()
            ->findAll()
            ->getRelated(array('articles', 'comments'))
            ->returnAs('json');
        exit;

    }
