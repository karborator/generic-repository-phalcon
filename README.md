# Phalcon-generic-repository
Generic repository for Phalcon-framework

Usa copy folder named repository and move somewhere.In my case it will be vendor.
At phalcon services file
  	   
  	    /**
             * Autoload repository
             */
            include __DIR_ . "/../../vendor/repository/autoload.php";
            
            
	    /**
             * Generic Repository
             */
            $dependencyInjector['repository'] = new GenericRepository($config, 'user');
            
Controller :

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
